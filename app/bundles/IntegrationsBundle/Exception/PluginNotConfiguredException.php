<?php

declare(strict_types=1);

namespace Mautic\IntegrationsBundle\Exception;

class PluginNotConfiguredException extends \Exception
{
    protected $message = 'mautic.integration.not_configured';
}
