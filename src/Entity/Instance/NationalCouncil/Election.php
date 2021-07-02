<?php

namespace App\Entity\Instance\NationalCouncil;

use App\Entity\VotingPlatform\Designation\AbstractElectionEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Instance\NationalCouncil\ElectionRepository")
 * @ORM\Table(name="national_council_election")
 */
class Election extends AbstractElectionEntity
{
}