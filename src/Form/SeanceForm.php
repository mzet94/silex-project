<?php
/**
 * Seance form.
 *
 * @author Marta Zięba <marta.zieba@student.uj.edu.pl>
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/seances
 * @copyright 2015 Marta Zięba
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Model\MoviesModel;
use Model\LocationsModel;

/**
 * Class SeanceForm.
 *
 * @category Epi
 * @package Form
 * @extends AbstractType
 * @use Symfony\Component\Form\AbstractType
 * @use Symfony\Component\Form\FormBuilderInterface
 * @use Symfony\Component\OptionsResolver\OptionsResolverInterface
 * @use Symfony\Component\Validator\Constraints as Assert
 */
class SeanceForm extends AbstractType
{

   
    /**
     * App.
     *
     * @access protected
     */

    protected $app = null;
  
   /**
     * Object constructor.
     *
     * @access public
     * @param Silex\Application $app Silex application
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    /**
     * Form builder.
     *
     * @access public
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @return FormBuilderInterface
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return  $builder->add(
            'id',
            'hidden',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Type(array('type' => 'digit'))
                )
            )
        )
        ->add(
            'movie_id',
            'choice',
            array(
                'choices' => $this->getMoviesList($this->app),
                'constraints' => array(
                    new Assert\NotBlank()
                )
            )
        )
        ->add(
            'location_id',
            'choice',
            array(
                'choices'=> $this->getLocationsList($this->app),
                'constraints' => array(
                    new Assert\NotBlank()
                )
            )
        )
        ->add(
            'hall',
            'choice',
            array(
                    'choices' => array(
                        '1' => 'Sala 1',
                        '2' => 'Sala 2',
                        '3' => 'Sala 3',
                        '4' => 'Sala 4'
                    ),
                    'multiple' => false,
                    'expanded' => false,
                )
        )
        ->add(
            'date',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Regex(array(
                        'pattern'     => '/^[2-9][0-9][0-9][0-9]-[0-1][0-9]-[0-3][0-9]?$/i',
                        'htmlPattern' => '^[2-9][0-9][0-9][0-9]-[0-1][0-9]-[0-3][0-9]+$',
                        'message' => 'Musisz wpisać datę. (np.: 2015-02-15)',
                        ))
                )
                )
        )
        ->add(
            'time',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Regex(array(
                        'pattern'     => '/^[0-2][0-9]:00:00$/i',
                        'htmlPattern' => '^[0-2][0-9]:00:00$',
                        'message' => 'Musisz podać godzinę. (np.: 20:00:00)',
                        ))
                    )
                )
        )
        ->add(
            'seats',
            'choice',
            array(
                    'choices' => array(
                        '50' => '50',
                        '150' => '150',
                        '250' => '250',
                    ),
                    'multiple' => false,
                    'expanded' => false,
                )
        )
        ->add(
            'price',
            'choice',
            array(
                    'choices' => array(
                        '15' => '15 zł',
                        '18' => '18 zł',
                        '25' => '25 zł',
                        '30' => '30 zł',
                        '32' => '32 zł',
                        '40' => '40 zł',
                    ),
                    'multiple' => false,
                    'expanded' => false,
                )
        );
    }
  
    /**
     * Gets form name.
     *
     * @access public
     *
     * @return string
     */
    public function getName()
    {
        return 'seanceForm';
    }
          
    /**
     * Prepares movies for id field.
     *
     * @access public
     *
     * @param Silex\Application $app Silex application
     *
     * @return array
     */
    protected function getMoviesList($app)
    {
        $moviesModel = new MoviesModel($app);
        $result = $moviesModel->getAll();
        
        $dict = array();
        foreach ($result as $movie) {
            $dict[$movie['id']] = $movie['title'];
            $dict[$movie['id']] = $movie['title'];
        }
        return $dict;
    }
    /**
     * Prepares locations for id field.
     *
     * @access public
     *
     * @param Silex\Application $app Silex application
     *
     * @return array
     */
    protected function getLocationsList($app)
    {
        $locationsModel = new LocationsModel($app);
        $result = $locationsModel->getAll();
        
        $dict = array();
        foreach ($result as $location) {
            $dict[$location['id']] = $location['name'];
            $dict[$location['id']] = $location['name'];
        }
        return $dict;
    }
}
