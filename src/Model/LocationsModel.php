<?php
/**
 * Locations model.
 *
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/locations
 * @author marta(dot)zieba(at)student(dot)uj(dot)edu(dot)pl
 * @copyright Marta Zięba 2015
 */

namespace Model;

use Silex\Application;

/**
 * Class Locations.
 *
 * @category Epi
 * @package Model
 * @use Silex\Application
 */
class LocationsModel
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
     * Gets locations for pagination.
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     *
     * @return array Result
     */
    public function getPaginatedLocations($page, $limit)
    {
        $pagesCount = $this->countLocationsPages($limit);
        $page = $this->getCurrentPageNumber($page, $pagesCount);
        $locations = $this->getLocationsPage($page, $limit);
        return array(
            'locations' => $locations,
            'paginator' => array('page' => $page, 'pagesCount' => $pagesCount)
        );
    }
    /**
     * Get all locations on page
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     * @param integer $pagesCount Number of all pages
     * @retun array Result
     */
    public function getLocationsPage($page, $limit)
    {
        $sql = '
            SELECT 
                id, city, street, number, name 
            FROM 
                locations 
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
     * Counts location pages.
     *
     * @access public
     * @param integer $limit Number of records on single page
     * @return integer Result
     */
    public function countLocationsPages($limit)
    {
        $pagesCount = 0;
        $sql = 'SELECT COUNT(*) as pages_count FROM locations';
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
     * Gets all locations.
     *
     * @access public
     * @return array Result
     */
    public function getAll()
    {
        try {
            $query = '
                SELECT 
                    id, city, street, number, name 
                FROM 
                    locations
			';
            return $this->db->fetchAll($query);
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }
    /**
     * Gets single location data.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function getLocation($id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)) {
                $query = '
                    SELECT 
                        id, city, street, number, name 
                    FROM 
                        locations
                WHERE 
                    id= ?
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
     * Add single location data.
     *
     * @access public
     * @param integer $id Record Id
     * @param string $city Record City
     * @param string $street Record Street
     * @param string $number Record Street number
     * @param string $name Record Name
     * @return array Result
     */

    /**
     * Save location.
     *
     * @access public
     * @param array $location Location data
     * @retun mixed Result
     */
    public function saveLocation($location)
    {
        if (isset($location['id'])
            && ($location['id'] != '')
            && ctype_digit((string)$location['id'])) {
            // update record
            $id = $location['id'];
            unset($location['id']);
            return $this->db->update('locations', $location, array('id' => $id));
        } else {
            // add new record
            return $this->db->insert('locations', $location);
        }
    }
}
