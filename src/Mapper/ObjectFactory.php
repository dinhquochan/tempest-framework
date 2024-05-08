<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\Container\Container;
use Tempest\Mapper\Exceptions\CannotMapDataException;

/** @template ClassType */
final class ObjectFactory
{
    private mixed $from;

    private mixed $to;

    private bool $isCollection = false;

    /** @var class-string[]|null */
    private ?array $mappers = null;

    public function __construct(
        private readonly MapperConfig $config,
        private readonly Container $container,
    ) {
    }

    /**
     * @template T of object
     * @param T|class-string<T> $objectOrClass
     * @return self<T>
     */
    public function forClass(mixed $objectOrClass): self
    {
        $this->to = $objectOrClass;

        return $this;
    }

    public function withData(mixed $data): self
    {
        $this->from = $data;

        return $this;
    }

    /**
     * @return self<ClassType[]>
     */
    public function collection(): self
    {
        $this->isCollection = true;

        return $this;
    }

    /**
     * @return ClassType
     */
    public function from(mixed $data)
    {
        return $this->mapObject(
            from: $data,
            to: $this->to,
            isCollection: $this->isCollection,
        );
    }

    /**
     * @template T of object
     * @param T|class-string<T> $to
     * @return T|T[]|mixed
     */
    public function to(mixed $to): mixed
    {
        return $this->mapObject(
            from: $this->from,
            to: $to,
            isCollection: $this->isCollection,
        );
    }

    /**
     * @template T of object
     * @param mixed $from
     * @param T|class-string<T> $to
     * @return T|mixed
     */
    public function map(mixed $from, mixed $to): mixed
    {
        return $this->mapObject(
            from: $from,
            to: $to,
            isCollection: $this->isCollection,
        );
    }

    private function mapObject(
        mixed $from,
        mixed $to,
        bool $isCollection,
    ): mixed {
        if ($isCollection && is_array($from)) {
            return array_map(
                fn (mixed $item) => $this->mapObject(
                    from: $item,
                    to: $to,
                    isCollection: false,
                ),
                $from,
            );
        }

        $mappers = $this->mappers ?? $this->config->mappers;

        foreach ($mappers as $mapperClass) {
            /** @var Mapper $mapper */
            $mapper = $this->container->get($mapperClass);

            if ($mapper->canMap(from: $from, to: $to)) {
                return $mapper->map(from: $from, to: $to);
            }
        }

        throw new CannotMapDataException($from, $to);
    }
}
