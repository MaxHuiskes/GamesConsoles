<?php

namespace App\Form;

use App\Collection\Condition;
use App\Entity\GameVersion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class GameVersionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Version',
                'help' => 'e.g. Physical, Digital, GOTY, PAL',
            ])
            ->add('condition', ChoiceType::class, [
                'label' => 'Condition',
                'choices' => Condition::CHOICES,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('prijs', PasswordType::class, [
                'label' => 'Prijs',
                'required' => false,
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
            ])
            ->add('fotoFile', FileType::class, [
                'label' => 'Photo (optional)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif']),
                ],
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            if (!$event->getForm()->isValid()) {
                return;
            }

            $prijs = $event->getForm()->get('prijs')->getData();
            if (null !== $prijs && '' !== $prijs) {
                $event->getData()->setPrijs($prijs);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GameVersion::class,
        ]);
    }
}
