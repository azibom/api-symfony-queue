<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SmsRepository")
 */
class Sms
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $body;

    /**
     * @ORM\Column(type="string", length=2048, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $created_at;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sendOrNot;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

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

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getSendOrNot(): ?int
    {
        return $this->sendOrNot;
    }

    public function setSendOrNot(?int $sendOrNot): self
    {
        $this->sendOrNot = $sendOrNot;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }
}
