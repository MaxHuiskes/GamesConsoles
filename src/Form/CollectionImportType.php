<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class CollectionImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', FileType::class, [
            'label' => 'JSON or CSV file',
            'constraints' => [
                new File(
                    maxSize: '5M',
                    mimeTypes: ['application/json', 'text/csv', 'text/plain'],
                ),
            ],
        ]);
    }
}
