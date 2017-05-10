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

  /** @ManyToOne(targetEntity="User", inversedBy="allTweets") */
  protected $publisher;

  /** @ManyToOne(targetEntity="User", inversedBy="originalTweets") */
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

  public function __construct($publisher, $text, $original = null) {
    $this->publisher = $publisher;
    $this->text = $text;
    $this->likes = 0;
    $this->retweets = 0;
    if(null === $original) {
      $this->isRetweet = false;
      $this->author = $publisher;
    } else {
      $this->isRetweet = true;
      $this->original = $original;
      $this->author = $original->getAuthor();
    }
    $this->createdAt = new \Datetime();
  }

  public static function createRetweet(User $publisher, Tweet $original) {
    $retweet = new Tweet($publisher, $original->getText(), $original);
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

  public function isRetweet() {
    return $this->isRetweet;
  }

  public function getInfoOnCreate() {
    return [ 'id' => $this->id ];
  }

  public function getInfo($apiRoot) {
    $info = [
      'id' => $this->id,
      'URI'=> $apiRoot.'/tweets/'.$this->id,
      'userLogin' => $this->publisher->getLogin(),
      'userTweetsURI' => $apiRoot.'/users/'.$this->publisher->getId().'/tweets',
      'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
      'isRetweet' => $this->isRetweet
    ];
    if($this->isRetweet) {
      $info['original'] = [
        'id' => $this->original->getId(),
        'URI' => $apiRoot.'/tweets/'.$this->original->getId(),
        'text' => $this->text,
        'userLogin' => $this->original->getAuthor()->getLogin(),
        'userTweetsURI' => $apiRoot.'/users/'.$this->original->getAuthor()->getId().'/tweets',
        'createdAt' => $this->original->createdAt->format('Y-m-d H:i:s'),
        'likes' => $this->original->getLikes(),
        'retweets' => $this->original->getRetweets()
      ];
    } else {
      $info['text'] = $this->text;
      $info['likes'] = $this->likes;
      $info['retweets']= $this->retweets;
    }
    return $info;
  }

  public function getAuthor() {
    return $this->author;
  }

  public function getOriginal() {
    return $this->original;
  }

  public function getLikes() {
    return $this->likes;
  }

  public function getCreatedAt() {
    return $this->createdAt;
  }

  public function getRetweets() {
    return $this->retweets;
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
