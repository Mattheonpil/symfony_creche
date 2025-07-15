<?php

namespace App\Form\Autocomplete;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\UX\Autocomplete\Attribute\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Doctrine\AbstractEntityAutocompleter;

#[AsEntityAutocompleteField]
class ParentUserAutocomplete extends AbstractEntityAutocompleter
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getEntityClass(): string
    {
        return User::class;
    }

    public function createFilteredQueryBuilder(string $query)
    {
        return $this->userRepository->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->andWhere('u.name LIKE :query OR u.email LIKE :query')
            ->setParameter('role', '%ROLE_PARENT%')
            ->setParameter('query', '%'.$query.'%');
    }

    public function getLabel(object $entity): string
    {
        /** @var User $entity */
        return $entity->getName() . ' (' . $entity->getEmail() . ')';
    }
} 