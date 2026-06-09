<?php

namespace App\Form;

use App\Entity\Console;
use App\Entity\ConsoleVersion;
use App\Entity\Game;
use App\Entity\User;
use App\Repository\ConsoleRepository;
use App\Repository\ConsoleVersionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $owner */
        $owner = $options['owner'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
            ])
            ->add('consoles', EntityType::class, [
                'class' => Console::class,
                'choice_label' => fn (Console $console) => sprintf('%s (%s)', $console->getName(), $console->getBrand()),
                'label' => 'Consoles',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'help' => 'Optional. Select consoles first to choose their versions below.',
                'query_builder' => fn (ConsoleRepository $repo) => $repo->createQueryBuilder('console')
                    ->join('console.brand', 'brand')
                    ->where('brand.owner = :owner')
                    ->setParameter('owner', $owner)
                    ->orderBy('console.name', 'ASC'),
            ])
            ->add('consoleVersions', EntityType::class, [
                'class' => ConsoleVersion::class,
                'choice_label' => fn (ConsoleVersion $version) => sprintf(
                    '%s · %s (%s)',
                    $version->getConsole()?->getName() ?? '?',
                    $version->getName(),
                    $version->getConsole()?->getBrand()?->getName() ?? '?'
                ),
                'label' => 'Console versions',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'help' => 'Optional. Only versions from the selected consoles are shown.',
                'query_builder' => fn (ConsoleVersionRepository $repo) => $repo->createQueryBuilder('version')
                    ->join('version.console', 'console')
                    ->join('console.brand', 'brand')
                    ->where('brand.owner = :owner')
                    ->setParameter('owner', $owner)
                    ->orderBy('console.name', 'ASC')
                    ->addOrderBy('version.name', 'ASC'),
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $game = $event->getData();
            if (!$game instanceof Game) {
                return;
            }

            $consoleIds = [];
            foreach ($game->getConsoles() as $console) {
                if ($console->getId() !== null) {
                    $consoleIds[] = $console->getId();
                }
            }

            foreach ($game->getConsoleVersions()->toArray() as $version) {
                $consoleId = $version->getConsole()?->getId();
                if ($consoleId === null || !in_array($consoleId, $consoleIds, true)) {
                    $game->removeConsoleVersion($version);
                }
            }
        });
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
