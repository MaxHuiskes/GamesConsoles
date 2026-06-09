<?php

namespace App\Form;

use App\Entity\Console;
use App\Entity\Game;
use App\Entity\User;
use App\Repository\ConsoleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $owner */
        $owner = $options['owner'];

        $builder
            ->add('consoles', EntityType::class, [
                'class' => Console::class,
                'choice_label' => fn (Console $console) => sprintf('%s (%s)', $console->getName(), $console->getBrand()),
                'label' => 'Consoles',
                'multiple' => true,
                'expanded' => false,
                'query_builder' => fn (ConsoleRepository $repo) => $repo->createQueryBuilder('console')
                    ->join('console.brand', 'brand')
                    ->where('brand.owner = :owner')
                    ->setParameter('owner', $owner)
                    ->orderBy('console.name', 'ASC'),
            ])
            ->add('name', TextType::class, [
                'label' => 'Name',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
        $resolver->setRequired('owner');
        $resolver->setAllowedTypes('owner', User::class);
    }
}
