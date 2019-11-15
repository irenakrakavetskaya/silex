<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
//use Hateoas\Configuration\Annotation as Hateoas;
//use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\JoinTable;
use App\Entity\Author;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookRepository")
 * @ORM\Table(name="books")
 */
class Book extends Entity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * Many books have Many authors.
     * @ManyToMany(targetEntity="Author", inversedBy="books", cascade={"persist"})
     * @JoinTable(name="books_authors")
     */
    protected $authors;

    /**
     * Many books belongs to Many orders.
     * @ManyToMany(targetEntity="Order", inversedBy="books")
     * @JoinTable(name="books_orders")
     * @Exclude
     */
    protected $orders;

    public function __construct() {
        $this->authors = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function addAuthor(Author $author = null)
    {
        $this->authors->add($author);
    }

    public function getAuthors()
    {
        return $this->authors;
    }

    public function getOrders()
    {
        return $this->orders;
    }
}

