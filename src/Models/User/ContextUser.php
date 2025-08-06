<?php /* vim: set colorcolumn=: */

namespace CarlBennett\Tools\Models\User;

class ContextUser extends \CarlBennett\Tools\Models\User\ActiveUser implements \JsonSerializable
{
    /**
     * The contextual user that is being operated upon by the upstream Controller.
     * This could be null, could match the $active_user, or another user reference.
     *
     * @var \CarlBennett\Tools\Libraries\User\User|null
     */
    public ?\CarlBennett\Tools\Libraries\User\User $context_user = null;

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), ['context_user' => $this->context_user]);
    }
}
