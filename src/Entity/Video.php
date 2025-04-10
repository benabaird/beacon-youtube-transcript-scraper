<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 11)]
    private ?string $videoId = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $published = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $transcript = null;

    /**
     * @var Collection<int, Set>
     */
    #[ORM\ManyToMany(targetEntity: Set::class, inversedBy: 'videos')]
    private Collection $sets;

    #[ORM\Column]
    private ?bool $hidden = null;

    public function __construct()
    {
        $this->sets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVideoId(): ?string
    {
        return $this->videoId;
    }

    public function setVideoId(string $videoId): static
    {
        $this->videoId = $videoId;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getPublished(): ?\DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(\DateTimeInterface $published): static
    {
        $this->published = $published;

        return $this;
    }

    public function hasTranscript(): bool
    {
        return !\is_null($this->transcript);
    }

    public function hasRetrievedTranscript(): bool
    {
        return $this->hasTranscript() && $this->transcript !== serialize([]);
    }

    public function getTranscript(): ?string
    {
        return $this->transcript;
    }

    public function setTranscript(?string $transcript): static
    {
        $this->transcript = $transcript;

        return $this;
    }

    public function getTranscriptText(): string
    {
        return $this->transcript ? implode(' ', unserialize($this->transcript)) : '';
    }

    public function getTranscriptWithTimestamps(): array
    {
        return $this->transcript ? unserialize($this->transcript) : [];
    }

    /**
     * @return Collection<int, Set>
     */
    public function getSets(): Collection
    {
        return $this->sets;
    }

    public function addSet(Set $set): static
    {
        if (!$this->sets->contains($set)) {
            $this->sets->add($set);
        }

        return $this;
    }

    public function removeSet(Set $set): static
    {
        $this->sets->removeElement($set);

        return $this;
    }

    public function inSet(Set $set): bool
    {
        return $this->sets->contains($set);
    }

    public function isHidden(): ?bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): static
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function toArray(): array {
        $data = [];

        foreach (new \ReflectionClass($this)->getProperties() as $property) {
            if ($property->getName() === 'hidden') {
                continue;
            }

            if ($property->getType()->getName() === Collection::class) {
                continue;
            }

            if ($property->getType()->getName() === DateTimeInterface::class) {
                $data[$property->getName()] = $property->getValue($this)->format('F j, Y');
                continue;
            }

            if ($property->getName() === 'transcript') {
                $data[$property->getName()] = $this->getTranscriptText();
                continue;
            }

            $data[$property->getName()] = $property->getValue($this);
        }

        return $data;
    }
}
