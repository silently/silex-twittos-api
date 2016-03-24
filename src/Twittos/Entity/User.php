<?php
namespace Twittos\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

/** @Entity @Table(name="users") @HasLifecycleCallbacks **/
class User
{
  /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
  protected $id;

  /** @Column(type="string", length=255, unique=true) **/
  protected $login;

  /** @Column(type="string", length=255) **/
  protected $password;

  /** @Column(type="string", length=255) **/
  protected $email;

  /** @Column(type="date") **/
  protected $created_at;

  public function __construct($login, $password, $email) {
    $this->login = $login;
    $this->password = $password;
    $this->email = $email;
    $this->created_at = new \Datetime();
  }

  /** @PrePersist */
  public function hashPassword() {
    $this->password = password_hash($this->password, PASSWORD_DEFAULT);
  }

  // Validations
  static public function loadValidatorMetadata(ClassMetadata $metadata) {
    // Login validation
    $metadata->addPropertyConstraint('login', new Assert\NotBlank(array('message' => "can't be blank")));
    $metadata->addPropertyConstraint('login', new Assert\Length(array('min' => 2, 'minMessage' => "must be at least {{ limit }} characters long")));
    // Password validation
    $metadata->addPropertyConstraint('password', new Assert\NotBlank(array('message' => "can't be blank")));
    $metadata->addPropertyConstraint('password', new Assert\Length(array('min' => 6, 'minMessage' => "must be at least {{ limit }} characters long")));
    // Password validation
    $metadata->addPropertyConstraint('email', new Assert\NotBlank(array('message' => "can't be blank")));
    $metadata->addPropertyConstraint('email', new Assert\Email(array('message' => "must be a valid email address")));
  }

  // API

  public function getId() {
    return $this->id;
  }

  public function getInfo() {
    return [ 'login' => $this->login, 'email' => $this->email ];
  }

  public function authenticate($tentativePassword) {
    return password_verify($tentativePassword, $this->password);
  }

}
