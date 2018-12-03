<?php
/**
 * Directors model.
 *
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/directors
 * @author marta(dot)zieba(at)student(dot)uj(dot)edu(dot)pl
 * @copyright Marta Zięba 2015
 */

namespace Model;

use Silex\Application;

/**
 * Class Directors.
 *
 * @category Epi
 * @package Model
 * @use Silex\Application
 */
class DirectorsModel
{
    /**
     * Db object.
     *
     * @access protected
     * @var Silex\Provider\DoctrineServiceProvider $db
     */
    protected $db;

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
     * Gets albums for pagination.
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     *
     * @return array Result
     */
    public function getPaginatedDirectors($page, $limit)
    {
        $pagesCount = $this->countDirectorsPages($limit);
        $page = $this->getCurrentPageNumber($page, $pagesCount);
        $directors = $this->getDirectorsPage($page, $limit);
        return array(
            'directors' => $directors,
            'paginator' => array('page' => $page, 'pagesCount' => $pagesCount)
        );
    }
    /**
     * Get all directors on page
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     * @param integer $pagesCount Number of all pages
     * @retun array Result
     */
    public function getDirectorsPage($page, $limit)
    {
        $sql = '
            SELECT 
                id, firstName, surname 
            FROM 
                directors 
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
     * Counts director pages.
     *
     * @access public
     * @param integer $limit Number of records on single page
     * @return integer Result
     */
    public function countDirectorsPages($limit)
    {
        $pagesCount = 0;
        $sql = 'SELECT COUNT(*) as pages_count FROM directors';
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
     *
     */
    public function getCurrentPageNumber($page, $pagesCount)
    {
        return (($page <= 1) || ($page > $pagesCount)) ? 1 : $page;
    }

    /**
     * Gets all directors.
     *
     * @access public
     * @return array Result
     */
    public function getAll()
    {
        try {
            $query = '
                SELECT 
                    id, firstName, surname 
                FROM 
                    directors
			';
            return $this->db->fetchAll($query);
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }

    /**
     * Gets single director data.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function getDirector($id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)) {
                $query = '
                    SELECT 
                        id, firstName, surname 
                    FROM 
                        directors 
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
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }
    /**
     * Add single director data.
     *
     * @access public
     * @param integer $id Record Id
     * @param string $firstName Record First name
     * @param string $surname Record Last name
     * @return array Result
     */

   /**
    * Save director.
    *
    * @access public
    * @param array $director Director data
    * @retun mixed Result
    */
    public function saveDirector($director)
    {
        if (isset($director['id'])
            && ($director['id'] != '')
            && ctype_digit((string)$director['id'])) {
            // update record
            $id = $director['id'];
            unset($director['id']);
            return $this->db->update('directors', $director, array('id' => $id));
        } else {
            // add new record
            return $this->db->insert('directors', $director);
        }
    }
     /**
     * Add director.
     *
     * @access public
     * @param integer $id Record Id
     * @param string $firstName Record First name
     * @param string $surname Record Last name
     * @return array Result
     */
    public function addDirector($id, $firstName, $surname)
    {
        try {
            if (($id != '') && ctype_digit((string)$id) && ($firstName != '')
            && ctype_digit((string)$firstName) && ($surname != '')
            && ctype_digit((string)$surname)) {
                $query = '
                    INSERT INTO
                        `directors` (`id`, `firstName`, `surname`) 
                    VALUES 
						('.$id.', '.$firstName.', '.$surname.');
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
     * Delete single director data.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function deleteDirector($id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)) {
                $query = '
                    DELETE FROM 
                        directors 
                    WHERE 
                        id = ?
				';
                return $this->db->delete('directors', array('id' => $id));
            } else {
                return array();
            }
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
 
    }
}
