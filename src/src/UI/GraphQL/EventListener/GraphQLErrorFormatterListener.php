<?php

declare(strict_types=1);

namespace App\UI\GraphQL\EventListener;

use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;

final class GraphQLErrorFormatterListener
{
    public function onErrorFormatting(ErrorFormattingEvent $event): void
    {
        $formattedError = $event->getFormattedError();

        $keysToRemove = ['locations', 'debugMessage', 'trace', 'extensions'];

        foreach ($keysToRemove as $key) {
            if ($formattedError->offsetExists($key)) {
                $formattedError->offsetUnset($key);
            }
        }
    }
}
