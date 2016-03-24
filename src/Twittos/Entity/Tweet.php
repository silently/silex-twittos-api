<?php
namespace Twittos\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

/** @Entity @Table(name="tweets") */
class Tweet
{
  /** @Id @Column(type="guid") @GeneratedValue(strategy="UUID") */
  protected $id;

  /** @Column(type="string", length=140) */
  protected $text;

  /** @ManyToOne(targetEntity="User", inversedBy="tweets") */
  protected $author;

  /** @Column(type="integer") */
  protected $likes;

  /** @Column(type="datetime") */
  protected $created_at;

  public function __construct($author, $text) {
    $this->author = $author;
    $this->text = $text;
    $this->likes = 0;
    $this->created_at = new \Datetime();
  }

  // Validations
  static public function loadValidatorMetadata(ClassMetadata $metadata) {
    // Login validation
    $metadata->addPropertyConstraint('author', new Assert\NotBlank(array('message' => "can't be blank")));

    // Password validation
    $metadata->addPropertyConstraint('text', new Assert\NotBlank(array('message' => "can't be blank")));
    $metadata->addPropertyConstraint('text', new Assert\Length(array('max' => 140, 'minMessage' => "must be at most {{ limit }} characters long")));
  }

  // API

  public function getId() {
    return $this->id;
  }

  public function liked() {
    $this->likes = $this->likes + 1;
  }

}