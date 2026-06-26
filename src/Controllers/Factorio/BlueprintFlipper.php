<?php

namespace CarlBennett\Tools\Controllers\Factorio;

use \CarlBennett\Tools\Libraries\Core\HttpCode;
use \CarlBennett\Tools\Libraries\Core\Router;
use \CarlBennett\Tools\Libraries\Factorio\Entity;

class BlueprintFlipper extends \CarlBennett\Tools\Controllers\Base
{
    public function __construct()
    {
        $this->model = new \CarlBennett\Tools\Models\Factorio\BlueprintFlipper();
    }

    public function invoke(?array $args): bool
    {
        if (Router::requestMethod() == Router::METHOD_POST)
        {
            $q = Router::query();
            $this->model->action = $q['action'] ?? null;
            $this->model->blueprint_in = $q['blueprint_in'] ?? null;

            if (!empty($this->model->blueprint_in) && !empty($this->model->action))
            {
                $this->process();
            }
        }

        $this->model->_responseCode = HttpCode::HTTP_OK;
        return true;
    }

    private function process(): void
    {
        $blueprint_in = $this->model->blueprint_in;

        if (\substr($blueprint_in, 0, 1) !== '0')
        {
            $this->model->error = 'Unexpected blueprint version: ' . \substr($blueprint_in, 0, 1);
            return;
        }

        $blueprint = \json_decode(\zlib_decode(\base64_decode(\substr($blueprint_in, 1))), true);

        if (!\is_array($blueprint))
        {
            $this->model->error = 'Failed to decode blueprint string.';
            return;
        }

        $tree = &$blueprint['blueprint'];
        $errors = [];

        switch ($this->model->action)
        {
            case 'none': break;
            case 'horizontal':
                $this->reflectEntities($tree, 'horizontal', $errors);
                $this->reflectTiles($tree, 'horizontal');
                break;
            case 'vertical':
                $this->reflectEntities($tree, 'vertical', $errors);
                $this->reflectTiles($tree, 'vertical');
                break;
            default:
                $this->model->error = 'Unknown action.';
                return;
        }

        if (!empty($errors))
        {
            $this->model->error = \implode("\n", \array_unique($errors));
        }

        $this->model->blueprint_json = \json_encode($blueprint, JSON_PRETTY_PRINT);
        $this->model->blueprint_out = '0' . \base64_encode(\zlib_encode(\json_encode($blueprint), ZLIB_ENCODING_DEFLATE, 9));
    }

    private function reflectEntities(array &$tree, string $axis, array &$errors): void
    {
        if (!isset($tree['entities'])) return;

        foreach ($tree['entities'] as &$entity_data)
        {
            $entity = new Entity($entity_data);
            $err = ($axis === 'horizontal') ? $entity->reflectHorizontal() : $entity->reflectVertical();
            if ($err !== '') $errors[] = $err;
            $entity_data = $entity->getData();
        }
    }

    private function reflectTiles(array &$tree, string $axis): void
    {
        if (!isset($tree['tiles'])) return;

        foreach ($tree['tiles'] as &$tile)
        {
            switch ($tile['name'])
            {
                case 'refined-hazard-concrete-right': $tile['name'] = 'refined-hazard-concrete-left'; break;
                case 'refined-hazard-concrete-left':  $tile['name'] = 'refined-hazard-concrete-right'; break;
                case 'hazard-concrete-right':         $tile['name'] = 'hazard-concrete-left'; break;
                case 'hazard-concrete-left':          $tile['name'] = 'hazard-concrete-right'; break;
            }
        }
    }
}
