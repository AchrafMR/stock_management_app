<?php

namespace App\Entity;

use App\Repository\ModelsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModelsRepository::class)]
class Models
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'models')]
    private Collection $users;

    /**
     * @var Collection<int, Smodels>
     */
    #[ORM\OneToMany(targetEntity: Smodels::class, mappedBy: 'models')]
    private Collection $Smodels;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->Smodels = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getRoles(): array
    {
        $roles=$this->roles;
        $roles[] = 'ROLE_USER';

        return $roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addModel($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeModel($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Smodels>
     */
    public function getSmodels(): Collection
    {
        return $this->Smodels;
    }

    public function addSmodel(Smodels $smodel): static
    {
        if (!$this->Smodels->contains($smodel)) {
            $this->Smodels->add($smodel);
            $smodel->setModels($this);
        }

        return $this;
    }

    public function removeSmodel(Smodels $smodel): static
    {
        if ($this->Smodels->removeElement($smodel)) {
            // set the owning side to null (unless already changed)
            if ($smodel->getModels() === $this) {
                $smodel->setModels(null);
            }
        }

        return $this;
    }
}
