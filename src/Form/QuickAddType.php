<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Console;
use App\Entity\User;
use App\Model\QuickAddData;
use App\Repository\BrandRepository;
use App\Repository\ConsoleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuickAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $owner */
        $owner = $options['owner'];

        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Add',
                'choices' => [
                    'Game' => QuickAddData::TYPE_GAME,
                    'Console' => QuickAddData::TYPE_CONSOLE,
                ],
                'expanded' => true,
            ])
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => ['autocomplete' => 'off', 'enterkeyhint' => 'done'],
            ])
            ->add('brand', EntityType::class, [
                'class' => Brand::class,
                'choice_label' => 'name',
                'label' => 'Brand',
                'required' => false,
                'placeholder' => 'Choose brand',
                'query_builder' => fn (BrandRepository $repo) => $repo->createQueryBuilder('brand')
                    ->where('brand.owner = :owner')
                    ->setParameter('owner', $owner)
                    ->orderBy('brand.name', 'ASC'),
            ])
            ->add('console', EntityType::class, [
                'class' => Console::class,
                'choice_label' => fn (Console $console) => sprintf('%s (%s)', $console->getName(), $console->getBrand()),
                'label' => 'Console',
                'required' => false,
                'placeholder' => 'Link to console (optional)',
                'query_builder' => fn (ConsoleRepository $repo) => $repo->createQueryBuilder('console')
                    ->join('console.brand', 'brand')
                    ->where('brand.owner = :owner')
                    ->setParameter('owner', $owner)
                    ->orderBy('console.name', 'ASC'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuickAddData::class,
        ]);
        $resolver->setRequired('owner');
        $resolver->setAllowedTypes('owner', User::class);
    }
}
