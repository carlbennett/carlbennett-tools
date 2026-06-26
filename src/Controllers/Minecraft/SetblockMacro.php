<?php

namespace CarlBennett\Tools\Controllers\Minecraft;

use \CarlBennett\Tools\Libraries\Core\HttpCode;
use \CarlBennett\Tools\Libraries\Core\Router;

class SetblockMacro extends \CarlBennett\Tools\Controllers\Base
{
    public function __construct()
    {
        $this->model = new \CarlBennett\Tools\Models\Minecraft\SetblockMacro();
    }

    public function invoke(?array $args): bool
    {
        if (Router::requestMethod() == Router::METHOD_POST)
        {
            $q = Router::query();

            $this->model->say_done = ($q['say_done'] ?? null) ? true : false;
            $this->model->slash = ($q['slash'] ?? null) ? true : false;

            $x1 = $q['x1'] ?? '';
            $x2 = $q['x2'] ?? '';
            $y1 = $q['y1'] ?? '';
            $y2 = $q['y2'] ?? '';
            $z1 = $q['z1'] ?? '';
            $z2 = $q['z2'] ?? '';

            $this->model->x1 = ($x1 !== '') ? (int) $x1 : null;
            $this->model->x2 = ($x2 !== '') ? (int) $x2 : null;
            $this->model->y1 = ($y1 !== '') ? (int) $y1 : null;
            $this->model->y2 = ($y2 !== '') ? (int) $y2 : null;
            $this->model->z1 = ($z1 !== '') ? (int) $z1 : null;
            $this->model->z2 = ($z2 !== '') ? (int) $z2 : null;

            $tile = $q['tile'] ?? '';
            $extra = $q['extra'] ?? '';
            $this->model->tile = ($tile !== '') ? $tile : null;
            $this->model->extra = ($extra !== '') ? $extra : null;

            if ($this->model->allFieldsSet())
            {
                $this->model->code = $this->generateCode();
            }
        }

        $this->model->_responseCode = HttpCode::HTTP_OK;
        return true;
    }

    private function generateCode(): string
    {
        $m = $this->model;

        $x1 = $m->x1; $x2 = $m->x2;
        $y1 = $m->y1; $y2 = $m->y2;
        $z1 = $m->z1; $z2 = $m->z2;

        if ($x2 < $x1) { [$x1, $x2] = [$x2, $x1]; }
        if ($y2 < $y1) { [$y1, $y2] = [$y2, $y1]; }
        if ($z2 < $z1) { [$z1, $z2] = [$z2, $z1]; }

        $prefix = $m->slash ? '/' : '';
        $suffix = $m->tile . (!empty($m->extra) ? ' ' . $m->extra : '');

        $lines = '';
        for ($x = $x1; $x <= $x2; ++$x)
            for ($y = $y1; $y <= $y2; ++$y)
                for ($z = $z1; $z <= $z2; ++$z)
                    $lines .= \sprintf('%sminecraft:setblock %d %d %d %s' . "\n", $prefix, $x, $y, $z, $suffix);

        return $lines . ($m->say_done ? $prefix . "minecraft:say Done.\n" : '');
    }
}
