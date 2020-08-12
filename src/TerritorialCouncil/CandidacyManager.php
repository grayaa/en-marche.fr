<?php

namespace App\TerritorialCouncil;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\CandidacyInvitation;
use App\VotingPlatform\Event\BaseCandidacyEvent;
use App\VotingPlatform\Event\TerritorialCouncilCandidacyEvent;
use App\VotingPlatform\Events as VotingPlatformEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CandidacyManager
{
    private $entityManager;
    private $dispatcher;

    public function __construct(ObjectManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->dispatcher = $eventDispatcher;
    }

    public function updateCandidature(Candidacy $candidacy): void
    {
        $isCreation = false;

        if (!$candidacy->getId()) {
            $isCreation = true;
            $this->entityManager->persist($candidacy);
        }

        if ($candidacy->isConfirmed()) {
            $candidacy->getBinome()->updateFromBinome();
        }

        $this->entityManager->flush();

        if ($isCreation) {
            $this->dispatcher->dispatch(
                VotingPlatformEvents::CANDIDACY_CREATED,
                new TerritorialCouncilCandidacyEvent($candidacy)
            );
        } else {
            $this->dispatcher->dispatch(
                VotingPlatformEvents::CANDIDACY_UPDATED,
                new TerritorialCouncilCandidacyEvent($candidacy)
            );
        }
    }

    public function removeCandidacy(Candidacy $candidacy): void
    {
        $this->entityManager->remove($candidacy);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(VotingPlatformEvents::CANDIDACY_REMOVED, new BaseCandidacyEvent($candidacy));
    }

    public function acceptInvitation(CandidacyInvitation $invitation, Candidacy $acceptedBy): void
    {
        $invitation->accept();

        $invitation->getCandidacy()->updateFromBinome();
        $invitation->getCandidacy()->confirm();
        $acceptedBy->confirm();

        $this->updateCandidature($acceptedBy);
    }

    public function declineInvitation(CandidacyInvitation $invitation): void
    {
        $invitation->decline();

        $this->entityManager->flush();
    }
}