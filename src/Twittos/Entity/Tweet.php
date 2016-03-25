<?php
namespace Twittos\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

/** @Entity @Table(name="tweets") */
class Tweet {

  /** @Id @Column(type="guid") @GeneratedValue(strategy="UUID") */
  protected $id;

  /** @Column(type="string", length=140) */
  protected $text;

  /** @ManyToOne(targetEntity="User", inversedBy="tweets") @JoinColumn(name="author_id", referencedColumnName="id") */
  protected $author;

  /** @Column(type="integer") */
  protected $likes;

  /** @Column(type="integer") */
  protected $retweets;

  /** @Column(type="boolean") */
  protected $isRetweet;

  /** @OneToOne(targetEntity="Tweet") */
  protected $original;

  /** @Column(type="datetime") */
  protected $createdAt;

  public function __construct($author, $text, $original) {
    $this->author = $author;
    $this->text = $text;
    $this->likes = 0;
    $this->retweets = 0;
    if(null === $original) {
      $this->isRetweet = false;
    } else {
      $this->isRetweet = true;
      $this->original = $original;
    }
    $this->createdAt = new \Datetime();
  }

  public static function createRetweet(Tweet $original) {
    $retweet = new Tweet($original->getAuthor(), $original->getText(), $original);
    return $retweet;
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

  public function getInfoOnCreate() {
    return [ 'id' => $this->id ];
  }

  public function getInfo($apiRoot, $deep = true) {
    // $tweet['authorURI'] = $apiRoot.'/users/'.$tweet['authorLogin'];
    // $tweet['createdAt'] = $tweet['createdAt']->format('Y-m-d H:i:s');

    $info = [
      'id' => $this->id,
      'URI'=> $apiRoot.'/tweets/'.$this->id,
      'text' => $this->text,
      'authorLogin' => $this->author->getLogin(),
      'authorURI' => $apiRoot.'/users/'.$this->author->getLogin(),
      'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
      'isRetweet' => $this->isRetweet
    ];
    if($deep && $this->isRetweet) {
      $originalInfo = $this->original->getInfo($apiRoot, false);
      $info['originalId'] = $originalInfo['id'];
      $info['originalURI'] = $originalInfo['URI'];
      $info['originalAuthorLogin'] = $originalInfo['authorLogin'];
      $info['originalAuthorURI'] = $originalInfo['authorURI'];
      $info['originalLikes'] = $originalInfo['likes'];
      $info['originalRetweets'] = $originalInfo['retweets'];
    } else {
      $info['likes'] = $this->likes;
      $info['retweets']= $this->retweets;
    }
    return $info;
  }

  public function getAuthor() {
    return $this->author;
  }

  public function getText() {
    return $this->text;
  }

  public function liked() {
    $this->likes = $this->likes + 1;
  }

  public function retweeted() {
    $this->retweets = $this->retweets + 1;
  }

}
