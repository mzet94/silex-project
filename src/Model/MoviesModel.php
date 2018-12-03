<?php
/**
 * Movies model.
 *
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/movies
 * @author marta(dot)zieba(at)student(dot)uj(dot)edu(dot)pl
 * @copyright Marta Zięba 2015
 */

namespace Model;

use Silex\Application;

/**
 * Class Movies.
 *
 * @category Epi
 * @package Model
 * @use Silex\Application
 */
class MoviesModel
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
     * Gets movies for pagination.
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     *
     * @return array Result
     */
    public function getPaginatedMovies($page, $limit)
    {
        $pagesCount = $this->countMoviesPages($limit);
        $page = $this->getCurrentPageNumber($page, $pagesCount);
        $movies = $this->getMoviesPage($page, $limit);
        return array(
            'movies' => $movies,
            'paginator' => array('page' => $page, 'pagesCount' => $pagesCount)
        );
    }
    /**
     * Get all movies on page
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     * @param integer $pagesCount Number of all pages
     * @retun array Result
     */
    public function getMoviesPage($page, $limit)
    {
        $sql = '
            SELECT 
                movies.id, title, firstName, surname 
            FROM 
                movies 
            LEFT JOIN 
                directors 
            ON 
                movies.director_id = directors.id 
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
     * Counts movie pages.
     *
     * @access public
     * @param integer $limit Number of records on single page
     * @return integer Result
     */
    public function countMoviesPages($limit)
    {
        $pagesCount = 0;
        $sql = 'SELECT COUNT(*) as pages_count FROM movies';
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
     * Gets all movies.
     *
     * @access public
     * @return array Result
     */
    public function getAll()
    {
        try {
            $query = '
                SELECT
                    movies.id, title, director_id
                FROM
                    movies 
                JOIN 
                    directors 
                ON 
                    movies.director_id = directors.id
			';
            return $this->db->fetchAll($query);
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }

    /**
     * Gets single movie data.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function getMovie($id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)) {
                $query = '
                    SELECT 
                        *
                    FROM 
                        movies 
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
     * Add single movie data.
     *
     * @access public
     * @param integer $id Record Id
     * @param string $title Record Title
     * @return array Result
     */

    /**
     * Save movie.
     *
     * @access public
     * @param array $movie Movie data
     * @retun mixed Result
     */
    public function saveMovie($movie)
    {
        if (isset($movie['id'])
            && ($movie['id'] != '')
            && ctype_digit((string)$movie['id'])) {
            // update record
            $id = $movie['id'];
            unset($movie['id']);
            return $this->db->update('movies', $movie, array('id' => $id));
        } else {
            // add new record
            return $this->db->insert('movies', $movie);
        }
    }
    /**
     * Add movie.
     *
     * @access public
     * @param integer $id Record Id
     * @param string $title Record Title
     * @param integer $director_id Record Director
     * @return array Result
     */
    public function addMovie($id, $title, $director_id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id) && ($title != '')
            && ctype_digit((string)$title)) {
                $query = '
                    INSERT INTO 
                        `movies` (`id`, `title`, `director_id`) 
                    VALUES 
						('.$id.', '.$title.', '.$director_id.');
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
     * Delete single movie data.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function deleteMovie($id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)) {
                $query = '
                    DELETE FROM 
                        movies 
                    WHERE 
                        id= ?
				';
                return $this->db->delete('movies', array('id' => $id));
            } else {
                return array();
            }
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }
}
