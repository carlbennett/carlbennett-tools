<?php

namespace CarlBennett\Tools\Exceptions;

use \CarlBennett\Tools\Libraries\Core\Template;

class TemplateNotFoundException extends \LogicException
{
    public Template $template;

    public function __construct(Template $template)
    {
        $this->template = $template;
        parent::__construct(sprintf(
            'Template file not found: %s', $template->getTemplateFile()
        ));
    }
}
