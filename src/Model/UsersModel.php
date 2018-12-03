<?php
/**
 * Users model.
 *
 * @author Marta Zięba <marta.zieba@student.uj.edu.pl>
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/users
 * @copyright Marta Zięba 2015
 */

namespace Model;

use Doctrine\DBAL\DBALException;
use Silex\Application;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Class Users.
 *
 * @category Epi
 * @package Model
 * @use Silex\Application
 */

class UsersModel
{
    /**
     * Db object.
     *
     * @access protected
     * @var Silex\Provider\DoctrineServiceProvider $db
     */
    protected $db;

    /**
     * App object.
     *
     * @access protected
     * @var DoctrineServiceProvider $db
     */
    protected $app = null;
    
    /**
     * Object constructor.
     *
     * @access public
     * @param Silex\Application $app Silex application
     */
    public function __construct(Application $app)
    {
        $this->db = $app['db'];
    }

    /**
     * Loads user by login.
     *
     * @access public
     * @param string $login User login
     * @throws UsernameNotFoundException
     * @return array Result
     */
    public function loadUserByLogin($login)
    {
        $user = $this->getUserByLogin($login);

        if (!$user || !count($user)) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $login)
            );
        }

        $roles = $this->getUserRoles($user['id']);

        if (!$roles || !count($roles)) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $login)
            );
        }

        return array(
            'login' => $user['login'],
            'password' => $user['password'],
            'roles' => $roles
        );
    }
    
    /**
     * Gets single user data.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */

    public function getUser($id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)) {
                $query = '
					SELECT 
						`users`.`id`, `login`, `password`, `roles`.`name`,
						`address`.`city`, `address`.`street`, `address`.`number`,
						`address`.`post`, `userDetails`.`firstName`, 
						`userDetails`.`surname`, `userDetails`.`email`, 
						`userDetails`.`phone`
					FROM 
						`users`
					LEFT JOIN
						`roles`
					ON
						`users`.`role_id` = `roles`.`id`
					LEFT JOIN
						`userDetails`
					ON
						`users`.`id` = `userDetails`.`user_id`
					LEFT JOIN
						`address`
					ON
						`userDetails`.`id` = `address`.`userDetail_id`
					WHERE 
						`users`.`id` = ?
				';
                
                $result = $this->db->fetchAssoc($query, array((int)$id));
                if (!$result) {
                    return array();
                } else {
                    return $result;
                }
            } else {
                return array();
            }
        } catch (Exception $e) {
            echo 'Caught exception: ' .  $e->getMessage() . "\n";
        }
    }
        
    /**
     * Gets single user details.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */

    public function getUserDetails($id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)) {
                $query = '
					SELECT 
						*
					FROM 
						userDetails
					WHERE 
						id = ?
				';
                
                $result = $this->db->fetchAssoc($query, array((int)$id));
                if (!$result) {
                    return array();
                } else {
                    return $result;
                }
            } else {
                return array();
            }
        } catch (Exception $e) {
            echo 'Caught exception: ' .  $e->getMessage() . "\n";
        }
    }
    /**
     * Gets single user details by user id.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */

    public function getUserDetailsByHisId($id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)) {
                $query = '
					SELECT 
						*
					FROM 
						userDetails
					WHERE 
						user_id = ?
				';
                
                $result = $this->db->fetchAssoc($query, array((int)$id));
                if (!$result) {
                    return array();
                } else {
                    return $result;
                }
            } else {
                return array();
            }
        } catch (Exception $e) {
            echo 'Caught exception: ' .  $e->getMessage() . "\n";
        }
    }
    /**
     * Gets single address by details id.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */

    public function getUserAddressByDetailsId($id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)) {
                $query = '
					SELECT 
						*
					FROM 
						address
					WHERE 
						userDetail_id = ?
				';
                
                $result = $this->db->fetchAssoc($query, array((int)$id));
                if (!$result) {
                    return array();
                } else {
                    return $result;
                }
            } else {
                return array();
            }
        } catch (Exception $e) {
            echo 'Caught exception: ' .  $e->getMessage() . "\n";
        }
    }
    /**
     * Gets user data by login.
     *
     * @access public
     * @param string $login User login
     *
     * @return array Result
     */
    public function getUserByLogin($login)
    {
        try {
            $query = '
              SELECT
                `id`, `login`, `password`, `role_id`
              FROM
                `users`
              WHERE
                `login` = :login
            ';
            $statement = $this->db->prepare($query);
            $statement->bindValue('login', $login, \PDO::PARAM_STR);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? array() : current($result);
        } catch (\PDOException $e) {
            return array();
        }
    }
    /**
     * Gets user id by his login.
     *
     * @access public
     *
     * @param string $nameofuser
     *
     * @return int $userid
     */
    public function getUserId($nameofuser)
    {
        $query = '
			SELECT 
				id 
			FROM 
				users 
			WHERE
				login 
			LIKE 
				?
		';
        $result = $this->db->fetchAssoc($query, array($nameofuser));
        $r = $result['id'];
        return $r;
    }
    /**
     * Gets user details id by user id.
     *
     * @access public
     * @param int $id Record User id
     * @return int $detail_id
     */
    public function getDetailsId($id)
    {
        $query = '
			SELECT 
				id 
			FROM 
				userDetails 
			WHERE
				user_id 
			LIKE 
				?
		';
        $result = $this->db->fetchAssoc($query, array($id));
        $r = $result['id'];
        return $r;
    }
    
    /**
     * Gets user roles by User ID.
     *
     * @access public
     * @param integer $userId User ID
     *
     * @return array Result
     */
    public function getUserRoles($userId)
    {
        try {
            $query = '
                SELECT
                    `roles`.`name` as `role`
                FROM
                    `users`
                INNER JOIN
                    `roles`
                ON 
					`users`.`role_id` = `roles`.`id`
                WHERE
                    `users`.`id` = :user_id
                ';
            $statement = $this->db->prepare($query);
            $statement->bindValue('user_id', $userId, \PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            if ($result && count($result)) {
                $result = current($result);
                $roles[] = $result['role'];
            }
            return $roles;
        } catch (\PDOException $e) {
            return $roles;
        }
    }
    /**
     * Gets users for pagination.
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     *
     * @return array Result
     */

    public function getPaginatedUsers($page, $limit)
    {
        $pagesCount = $this->countUsersPages($limit);
        $page = $this->getCurrentPageNumber($page, $pagesCount);
        $users = $this->getUsersPage($page, $limit);
        return array(
            'users' => $users,
            'paginator' => array('page' => $page, 'pagesCount' => $pagesCount)
        );
    }
    /**
     * Counts users pages.
     *
     * @access public
     * @param integer $limit Number of records on single page
     * @return integer Result
     */

    public function countUsersPages($limit)
    {
        $pagesCount = 0;
        $sql = '
			SELECT COUNT(*) 
				as pages_count 
			FROM 
				users
		';
        $result = $this->db->fetchAssoc($sql);
        if ($result) {
            $pagesCount = ceil($result['pages_count']/$limit);
        }
        return $pagesCount;
    }
    /**
     * Returns current page number.
     *
     * @access public
     * @param integer $page Page number
     * @param integer $pagesCount Number of all pages
     * @return integer Page number
     */

    public function getCurrentPageNumber($page, $pagesCount)
    {
        return (($page <= 1) || ($page > $pagesCount)) ? 1 : $page;
    }
    /**
     * Get all users on page
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     *
     * @return array Result
     */

    public function getUsersPage($page, $limit)
    {
        $sql = '
			SELECT 
				users.id, login, roles.name 
			FROM 
				users 
			LEFT JOIN
				roles
			ON	
				users.role_id = roles.id
			LIMIT 
				:start, :limit
		';
        $statement = $this->db->prepare($sql);
        $statement->bindValue('start', ($page-1)*$limit, \PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
    /**
     * Gets all users.
     *
     * @access public
     * @return array Result
     */
    public function getAll()
    {
        try {
            $query = '
				SELECT
					id, login, role_id
				FROM 
					users
			';
            return $this->db->fetchAll($query);
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }

    /**
     * Save user.
     *
     * @access public
     * @param array $user User data
     * @return mixed Result
     */
    public function saveUser($user)
    {
        if (isset($user['id'])
            && ($user['id'] != '')
            && ctype_digit((string)$user['id'])) {
            // update record
            $id = $user['id'];
            unset($user['id']);
            return $this->db->update('users', $user, array('id' => $id));
        } else {
            // add new record
            return $this->db->insert('users', $user);
        }
    }
    /**
     * Register user.
     *
     * @access public
     * @param integer $id Record Id
     * @param string $login Record Login
     * @param string $password Record Password
     * @param integer $role_id Record Role
     * @return array Result
     */
    public function registerUser($id, $login, $password, $role_id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)
            && ($login != '') && ctype_digit((string)$login)
            && ($password != '') && ctype_digit((string)$password)
            && ($role_id != '') && ctype_digit((string)$role_id)) {
                $query = '
					INSERT INTO 
						`users` 
							(`id`, `login`, `password`, `role_id`) 
				
				    VALUES 
							('.$id.', '.$login.', '.$password.', '.$role_id.');
				';
                return $this->db->fetchAssoc($query, array((int)$id));
            } else {
                return array();
            }
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }
    /**
     * Save user address.
     *
     * @access public
     * @param array $address Address data
     * @return mixed Result
     */
    public function saveAddress($address)
    {
        if (isset($address['id'])
            && ($address['id'] != '')
            && ctype_digit((string)$address['id'])) {
            // update record
            $id = $address['id'];
            unset($address['id']);
            return $this->db->update('address', $address, array('id' => $id));
        } else {
            // add new record
            return $this->db->insert('address', $address);
        }
    }
    /**
     * Save user details.
     *
     * @access public
     * @param array $user User data
     * @return mixed Result
     */
    public function saveUserDetails($details)
    {
        if (isset($details['id'])
            && ($details['id'] != '')
            && ctype_digit((string)$details['id'])) {
            // update record
            $id = $details['id'];
            unset($details['id']);
            return $this->db->update('userDetails', $details, array('id' => $id));
        } else {
            // add new record
            return $this->db->insert('userDetails', $details);
        }
    }
    /**
     * Add user details.
     *
     * @access public
     * @param integer $id Record Id
     * @param integer $user_id Record User Id
     * @param string $firstName Record First name
     * @param string $surname Record Surname
     * @param integer $phone Record Phone number
     * @param string $email Record E-mail
     * @return array Result
     */
    public function addUserDetails($id, $user_id, $firstName, $surname, $phone, $email)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)
            && ($user_id != '') && ctype_digit((string)$user_id)
            && ($firstName != '') && ctype_digit((string)$firstName)
            && ($surname != '') && ctype_digit((string)$surname)
            && ($phone != '')   && ctype_digit((string)$phone)
            && ($email != '') && ctype_digit((string)$email)) {
                $query = '
					INSERT INTO 
						`userDetails` 
							(`id`, `user_id`, `firstName`, 
							`surname`, `phone`, `email`) 
				
				    VALUES 
							('.$id.', '.$user_id.','.$firstName.', '.$surname.', 
							'.$phone.', '.$email.');
				';
                return $this->db->fetchAssoc($query, array((int)$id));
            } else {
                return array();
            }
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }
    /**
     * Edit single user data.
     *
     * @access public
     * @param integer $detail_id Record Id
     * @param integer $id Record User Id
     * @param string $firstName Record Name
     * @param string $surname Record Surname
     * @param integer $phone Record Phone number
     * @param integer $email Record E-mail
     * @return array Result
     */

    public function editUser($id, $user_id, $firstName, $surname, $phone, $email)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)
                && ($user_id != '') && ctype_digit((string)$user_id)
                && ($firstName != '') && ctype_digit((string)$firstName)
                && ($surname != '') && ctype_digit((string)$surname)
                && ($phone != '')   && ctype_digit((string)$phone)
                && ($email != '') && ctype_digit((string)$email)) {
                $query = '
					UPDATE 
						`userDetails` 
					SET 
						`user_id` = '.$user_id.', `firstName` = '.$firstName.', 
						`surname` = '.$surname.', `phone` = '.$phone.', 
						`email` = '.$email.'
					WHERE 
						`id` = '.$id.';
				';
                return $this->db->fetchAssoc($query, array((int)$id));
            } else {
                return array();
            }
        } catch (Exception $e) {
            echo 'Caught exception: ' .  $e->getMessage() . "\n";
        }
    }
    
    /**
     * Delete single user data.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function deleteUser($id)
    {
        try {
            if (($id != '')
                && ctype_digit((string)$id) ) {
                $query = '
				  DELETE FROM 
				    users 
				  WHERE 
				    id= ?
				';
                return $this->db->delete('users', array('id' => $id));
            } else {
                return array();
            }
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }
}
