<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseResolver implements ParamConverterInterface
{
    private ServiceEntityRepository $repository;

    private string $class;

    private string $name;

    public function __construct(ServiceEntityRepository $repository)
    {
        $this->repository = $repository;
        $this->class = $repository->getClassName();
        $this->name = strtolower((new \ReflectionClass($this->class))->getShortName());
    }

    public function supports(ParamConverter $configuration): bool
    {
        if ($this->class !== $configuration->getClass() || $this->name !== $configuration->getName()) {
            return false;
        }

        return true;
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        if (!$id = $request->get($this->name.'_id')) {
            return false;
        }

        if (!$object = $this->repository->find($id)) {
            throw new \InvalidArgumentException('Entity with this id doesn\'t exists');
        }

        $request->attributes->set($configuration->getName(), $object);
        return true;
    }

}