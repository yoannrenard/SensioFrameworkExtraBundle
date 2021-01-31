<?php

declare(strict_types=1);

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

final class UidParamConverter implements ParamConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $param = $configuration->getName();
        $class = $configuration->getClass();

        if (!$request->attributes->has($param)) {
            return false;
        }
        $value = $request->attributes->get($param);

        $request->attributes->set($param, $class::fromString($value));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        if (!class_exists(AbstractUid::class)) {
            return false;
        }

        if (null === $configuration->getClass()) {
            return false;
        }

        return is_subclass_of($configuration->getClass(), AbstractUid::class);
    }
}
