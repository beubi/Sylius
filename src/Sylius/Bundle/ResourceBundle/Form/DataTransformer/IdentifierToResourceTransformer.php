<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\ResourceBundle\Form\DataTransformer;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Webmozart\Assert\Assert;

/**
 * @author Anna Walasek <anna.walasek@lakion.com>
 */
class IdentifierToResourceTransformer implements DataTransformerInterface
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @param RepositoryInterface $repository
     * @param string $identifier
     */
    public function __construct(RepositoryInterface $repository, $identifier = 'id')
    {
        $this->repository = $repository;
        $this->identifier = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        $resource = $this->repository->findOneBy([$this->identifier => $value]);
        if (null === $resource) {
            throw new TransformationFailedException(sprintf(
                'Object "%s" with identifier "%s"="%s" does not exist.',
                $this->repository->getClassName(),
                $this->identifier,
                $value
            ));
        }

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        }

        $class = $this->repository->getClassName();
        Assert::isInstanceOf($value, $class);

        $accessor = PropertyAccess::createPropertyAccessor();

        return $accessor->getValue($value, $this->identifier);
    }
}
