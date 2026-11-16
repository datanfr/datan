<?php

namespace App\Command;

use App\Entity\Utilisateur;
use App\Repository\DeputeRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Crée un compte de connexion, ou réinitialise son mot de passe.
 *
 * Il n'y a pas d'inscription en ligne : les comptes sont ouverts à la main,
 * comme dans l'application d'origine. Le legacy servait bien un
 * `/demande-compte-depute`, mais c'est un formulaire de demande — la rédaction
 * ouvre le compte ensuite.
 *
 *     php bin/console app:utilisateur:creer jdupont "Jean Dupont" --admin
 *     php bin/console app:utilisateur:creer jdupont --depute=jean-dupont
 */
#[AsCommand(
    name: 'app:utilisateur:creer',
    description: 'Crée un compte rédacteur, administrateur ou député.',
)]
class CreerUtilisateurCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UtilisateurRepository $utilisateurs,
        private readonly DeputeRepository $deputes,
        private readonly UserPasswordHasherInterface $hacheur,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('identifiant', InputArgument::REQUIRED, 'Identifiant de connexion')
            ->addArgument('nom', InputArgument::OPTIONAL, 'Nom affiché à côté des décryptages')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Donne le rôle administrateur')
            ->addOption('depute', null, InputOption::VALUE_REQUIRED, 'Slug du député dont ce compte est l\'espace personnel')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Adresse électronique')
            ->addOption('mot-de-passe', null, InputOption::VALUE_REQUIRED, 'Mot de passe (demandé si absent)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $identifiant = (string) $input->getArgument('identifiant');

        if ($input->getOption('depute') && $input->getOption('admin')) {
            $io->error('Un compte est celui d\'un député ou celui de la rédaction, jamais les deux.');

            return Command::FAILURE;
        }

        $depute = null;

        if ($slug = $input->getOption('depute')) {
            $depute = $this->deputes->findOneBy(['slug' => $slug]);

            if ($depute === null) {
                $io->error(sprintf('Aucun député de slug « %s ».', $slug));

                return Command::FAILURE;
            }
        }

        $motDePasse = (string) ($input->getOption('mot-de-passe') ?: $io->askHidden('Mot de passe'));

        if (mb_strlen($motDePasse) < 12) {
            $io->error('Le mot de passe doit faire au moins 12 caractères.');

            return Command::FAILURE;
        }

        $utilisateur = $this->utilisateurs->findOneBy(['identifiant' => $identifiant]);
        $nouveau = $utilisateur === null;

        if ($nouveau) {
            $utilisateur = new Utilisateur();
            $utilisateur->setIdentifiant($identifiant);
            $utilisateur->setNom((string) ($input->getArgument('nom')
                ?: ($depute !== null ? $depute->getFirstname() . ' ' . $depute->getLastname() : $identifiant)));
        } elseif ($input->getArgument('nom')) {
            $utilisateur->setNom((string) $input->getArgument('nom'));
        }

        if ($email = $input->getOption('email')) {
            $utilisateur->setEmail((string) $email);
        }

        if ($depute !== null) {
            $utilisateur->setDepute($depute);
            $utilisateur->setRoles([]);
        }

        if ($input->getOption('admin')) {
            $utilisateur->setRoles([Utilisateur::ROLE_ADMIN]);
        }

        $utilisateur->setPassword($this->hacheur->hashPassword($utilisateur, $motDePasse));

        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        $io->success(sprintf(
            '%s « %s » (%s).',
            $nouveau ? 'Compte créé' : 'Mot de passe réinitialisé pour',
            $utilisateur->getNom(),
            match (true) {
                $utilisateur->estDepute() => 'député',
                $utilisateur->estAdmin() => 'administrateur',
                default => 'rédacteur',
            },
        ));

        return Command::SUCCESS;
    }
}
