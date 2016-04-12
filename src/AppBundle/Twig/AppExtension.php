<?php
namespace AppBundle\Twig;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
          new \Twig_SimpleFilter('md5', array($this, 'md5Filter'))
        );
    }

    public function getName()
    {
        return 'app_extension';
    }

    /**
     * New filter for twig in order to have a md5 value of a string
     * 
     * @param string $value
     * @return string
     */
    public function md5Filter(string $value)
    {
        return md5($value);
    }


}

