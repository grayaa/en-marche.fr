<?php

namespace App\Entity\Event;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "pagination_enabled": false,
 *         "order": {"slug": "ASC"},
 *         "normalization_context": {
 *             "groups": {"event_category_read"}
 *         },
 *     },
 *     itemOperations={"get"},
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/event_categories",
 *         },
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\EventCategoryRepository")
 * @ORM\Table(
 *     name="events_categories",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="event_category_name_unique", columns="name"),
 *         @ORM\UniqueConstraint(name="event_category_slug_unique", columns="slug")
 *     }
 * )
 *
 * @UniqueEntity("name")
 */
class EventCategory extends BaseEventCategory
{
    /**
     * @var EventGroupCategory|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Event\EventGroupCategory", inversedBy="eventCategories")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank
     *
     * @Groups({"event_read", "event_list_read"})
     */
    private $eventGroupCategory;

    public function getEventGroupCategory(): ?EventGroupCategory
    {
        return $this->eventGroupCategory;
    }

    public function setEventGroupCategory(EventGroupCategory $eventGroupCategory): void
    {
        $this->eventGroupCategory = $eventGroupCategory;
    }
}
