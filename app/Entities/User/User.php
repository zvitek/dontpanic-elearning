<?php
namespace DontPanic\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable;
use Nette\Security\Passwords;
use Nette\Utils\Random;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

/** @noinspection PhpDeprecationInspection */

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity
 */
class User
{

    use MagicAccessors;
    use SoftDeletable;

    const PHONE_CODE = '+420';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Serializer\Groups({ "apiV1_list", "apiV1_detail" })
     * @Serializer\SerializedName("id")
     * @Serializer\Type("integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=70, nullable=true)
     *
     * @Serializer\Groups({ "apiV1_list", "apiV1_detail" })
     * @Serializer\SerializedName("username")
     * @Serializer\Type("string")
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=30, nullable=true)
     *
     * @Serializer\Groups({ "apiV1_list", "apiV1_detail" })
     * @Serializer\SerializedName("name")
     * @Serializer\Type("string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=30, nullable=true)
     *
     * @Serializer\Groups({ "apiV1_list", "apiV1_detail" })
     * @Serializer\SerializedName("surname")
     * @Serializer\Type("string")
     */
    private $surname;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=80, nullable=false)
     *
     * @Serializer\Groups({ "apiV1_list", "apiV1_detail" })
     * @Serializer\SerializedName("email")
     * @Serializer\Type("string")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=9, nullable=true)
     *
     * @Serializer\Groups({ "apiV1_list", "apiV1_detail" })
     * @Serializer\SerializedName("phone")
     * @Serializer\Type("string")
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="sex", type="string", length=6, nullable=true)
     *
     * @Serializer\Groups({ "apiV1_list", "apiV1_detail" })
     * @Serializer\SerializedName("sex")
     * @Serializer\Type("string")
     */
    private $sex;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="password_token", type="string", length=30, nullable=true)
     */
    private $passwordToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="password_expiration_at", type="datetime", nullable=true)
     */
    private $passwordExpirationAt;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=30, nullable=false)
     */
    private $token;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="number_of_logins", type="integer", nullable=false)
     */
    private $numberOfLogins = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_login_at", type="datetime", nullable=true)
     */
    private $lastLoginAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="AclRole", inversedBy="users")
     * @ORM\JoinTable(name="acl_users_roles",
     *   joinColumns={
     *     @ORM\JoinColumn(name="users_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="acl_roles_id", referencedColumnName="id")
     *   }
     * )
     */
    private $userRoles;

    /**
     * @var array|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ApiToken", mappedBy="user", cascade={"persist"})
     **/
    protected $apiTokens;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
        $this->apiTokens = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username ?: null;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        if (strpos($name, ' ') !== false) {
            list($firstname, $lastname) = explode(' ', $name, 2);
            $this->name    = $firstname;
            $this->surname = $lastname;
        } else {
            $this->name = $name ?: null;
        }

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set surname
     *
     * @param string $surname
     *
     * @return User
     */
    public function setSurname($surname)
    {
        $this->surname = $surname ?: null;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param bool $reverse
     *
     * @return string
     */
    public function getFullName($reverse = false)
    {
        if ($reverse) {
            return sprintf('%s %s', $this->surname, $this->name);
        }

        return sprintf('%s %s', $this->name, $this->surname);
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        if (Validators::isEmail($email)) {
            $this->email = Strings::lower($email);
        }

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param $phone
     *
     * @return $this
     */
    public function setPhone($phone)
    {
        $phone = str_replace(' ', '', $phone);
        if (preg_match('/^(\d{9}+)$/', $phone)) {
            $this->phone = $phone;
        }

        return $this;
    }

    /**
     * @param bool $formated
     *
     * @return string
     */
    public function getPhone($formated = true)
    {
        if ($formated && $this->phone) {
            return sprintf('%s %s', self::PHONE_CODE, chunk_split($this->phone, 3, ' '));
        }

        return $this->phone;
    }

    /**
     * Set sex
     *
     * @param string $sex
     *
     * @return User
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * Get sex
     *
     * @return string
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = Passwords::hash($password);

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set passwordExpirationAt
     *
     * @param string $passwordExpirationAt
     *
     * @return User
     */
    public function setPasswordExpirationAt($passwordExpirationAt)
    {
        $this->passwordExpirationAt = $passwordExpirationAt;

        return $this;
    }

    /**
     * Get passwordExpirationAt
     *
     * @return string
     */
    public function getPasswordExpirationAt()
    {
        return $this->passwordExpirationAt;
    }

    /**
     * Set passwordToken
     *
     * @param string $passwordToken
     *
     * @return User
     */
    public function setPasswordToken($passwordToken)
    {
        $this->passwordToken = $passwordToken;

        return $this;
    }

    /**
     * Get passwordToken
     *
     * @return string
     */
    public function getPasswordToken()
    {
        return $this->passwordToken;
    }

    /**
     * @param null $token
     *
     * @return $this
     */
    public function setToken($token = null)
    {
        $this->token = $token ?? Random::generate(30, '0-9a-zA-Z');

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return User
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return \DateTime|string
     */
    public function getCreatedAt($format = 'd. m. Y H:i')
    {
        if ($format) {
            return $this->createdAt->format($format);
        }

        return $this->createdAt;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return User
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set lastLoginAt
     *
     * @param \DateTime $lastLoginAt
     *
     * @return User
     */
    public function setLastLoginAt(\DateTime $lastLoginAt)
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    /**
     * Get lastLoginAt
     *
     * @return \DateTime
     */
    public function getLastLoginAt(): \DateTime
    {
        return $this->lastLoginAt;
    }

    /**
     * @return $this
     */
    public function setNumberOfLogins()
    {
        ++$this->numberOfLogins;

        return $this;
    }

    /**
     * Get numberOfLogins
     *
     * @return integer
     */
    public function getNumberOfLogins(): int
    {
        return $this->numberOfLogins;
    }

    /**
     * @param AclRole $userRole
     *
     * @return $this
     */
    public function addRole(AclRole $userRole)
    {
        $this->userRoles[] = $userRole;

        return $this;
    }

    /**
     * Remove userRoles
     *
     * @param AclRole $userRole
     */
    public function removeRole(AclRole $userRole)
    {
        $this->userRoles->removeElement($userRole);
    }

    /**
     * @param bool $names
     *
     * @return array|ArrayCollection|Collection|static
     */
    public function getRoles($names = false)
    {
        if (!count($this->userRoles)) {
            return [];
        }

        if ($names) {
            $roles = [];

            $this->userRoles->filter(function (AclRole $role) use (&$roles) {
                $roles[] = $role->getKeyName();
            });

            return $roles;
        }

        return $this->userRoles;
    }

    /**
     * @return array|ArrayCollection
     */
    public function getApiTokens()
    {
        return $this->apiTokens;
    }

    /**
     * @param ApiToken $apiToken
     *
     * @return $this
     */
    public function addApiToken(ApiToken $apiToken)
    {
        $this->apiTokens[] = $apiToken;

        return $this;
    }

    /**
     * @param ApiToken $apiToken
     */
    public function removeApiToken(ApiToken $apiToken)
    {
        $this->apiTokens->removeElement($apiToken);
    }
}