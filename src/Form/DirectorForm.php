<?php
/**
 * Director form.
 *
 * @author Marta Zięba <marta.zieba@student.uj.edu.pl>
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/directors
 * @copyright 2015 Marta Zięba
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Model\MoviesModel;

/**
 * Class DirectorForm.
 *
 * @category Epi
 * @package Form
 * @extends AbstractType
 * @use Symfony\Component\Form\AbstractType
 * @use Symfony\Component\Form\FormBuilderInterface
 * @use Symfony\Component\OptionsResolver\OptionsResolverInterface
 * @use Symfony\Component\Validator\Constraints as Assert
 */
class DirectorForm extends AbstractType
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
            'firstName',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 3))
                )
            )
        )
        ->add(
            'surname',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 3))
                )
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
        return 'directorForm';
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
            $dict[$movie['id']] = $movie['name'];
        }
        return $dict;
    }
}
