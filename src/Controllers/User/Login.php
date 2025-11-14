<?php

namespace CarlBennett\Tools\Controllers\User;

use \CarlBennett\Tools\Libraries\Core\HttpCode;
use \CarlBennett\Tools\Libraries\Core\Router;
use \CarlBennett\Tools\Libraries\User\User;

class Login extends \CarlBennett\Tools\Controllers\Base
{
    public function __construct()
    {
        $this->model = new \CarlBennett\Tools\Models\User\Authentication();
    }

    public function invoke(?array $args): bool
    {
        $this->model->feedback = [];

        $q = Router::query();
        $this->model->email = $q['email'] ?? null;
        $this->model->password = $q['password'] ?? null;

        $return = $q['return'] ?? null;
        if (!empty($return) && substr($return, 0, 1) != '/') $return = null;
        if (!empty($return)) $return = \CarlBennett\Tools\Libraries\Core\UrlFormatter::format($return);
        $this->model->return = $return;

        $this->model->_responseCode = HttpCode::HTTP_OK;
        if (Router::requestMethod() == Router::METHOD_POST) $this->processLogin();

        return true;
    }

    protected function processLogin(): void
    {
        $model = $this->model;

        if (empty($model->email))
        {
            $model->feedback['email'] = 'Email cannot be empty.';
            $model->error = $model->feedback['email'];
            return;
        }

        if (!filter_var($model->email, FILTER_VALIDATE_EMAIL))
        {
            $model->feedback['email'] = 'Invalid email address.';
            $model->error = $model->feedback['email'];
            return;
        }

        $user = User::getByEmail($model->email);

        if (!$user)
        {
            $model->feedback['email'] = 'User not found.';
            $model->error = $model->feedback['email'];
            return;
        }

        $check = $user->checkPassword($model->password);

        if (!($check & User::PASSWORD_CHECK_VERIFIED))
        {
            $model->feedback['password'] = 'Incorrect password.';
            $model->error = $model->feedback['password'];
            return;
        }

        if ($user->isBanned())
        {
            $model->feedback['email'] = 'Account is banned.';
            $model->error = $model->feedback['email'];
            return;
        }

        if ($check & User::PASSWORD_CHECK_UPGRADE)
        {
            // Upgrade with provided password, it is verified in previous step
            $user->setPasswordHash(User::createPassword($model->password));
            $user->commit();
        }

        $model->error = !\CarlBennett\Tools\Libraries\Core\Authentication::login($user);

        if (!empty($model->return))
        {
            $model->_responseCode = HttpCode::HTTP_SEE_OTHER;
            $model->_responseHeaders['Location'] = $model->return;
        }
    }
}
