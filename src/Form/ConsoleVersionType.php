<?php

namespace App\Form;

use App\Entity\ConsoleVersion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ConsoleVersionType extends AbstractType
{
    public const CONDITIONS = [
        'Mint' => 'mint',
        'Good' => 'good',
        'Fair' => 'fair',
        'Poor' => 'poor',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Version',
                'help' => 'e.g. Slim, Digital Edition, PAL',
            ])
            ->add('condition', ChoiceType::class, [
                'label' => 'Condition',
                'choices' => self::CONDITIONS,
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
            'data_class' => ConsoleVersion::class,
        ]);
    }
}
