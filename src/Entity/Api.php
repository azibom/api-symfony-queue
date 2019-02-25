<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ApiRepository")
 */
class Api
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2048, nullable=true)
     */
    private $api_url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $send_or_not;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $created_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApiUrl(): ?string
    {
        return $this->api_url;
    }

    public function setApiUrl(?string $api_url): self
    {
        $this->api_url = $api_url;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSendOrNot(): ?int
    {
        return $this->send_or_not;
    }

    public function setSendOrNot(?int $send_or_not): self
    {
        $this->send_or_not = $send_or_not;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}
