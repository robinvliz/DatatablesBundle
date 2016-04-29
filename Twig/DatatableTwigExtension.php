<?php

/**
 * This file is part of the SgDatatablesBundle package.
 *
 * (c) stwe <https://github.com/stwe/DatatablesBundle>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sg\DatatablesBundle\Twig;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use Sg\DatatablesBundle\Datatable\Column\AbstractColumn;

use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFunction;
use Twig_SimpleFilter;
use Exception;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class DatatableTwigExtension
 *
 * @package Sg\DatatablesBundle\Twig
 */
class DatatableTwigExtension extends Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    //-------------------------------------------------
    // Ctor.
    //-------------------------------------------------

    /**
     * Ctor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    //-------------------------------------------------
    // Twig_ExtensionInterface
    //-------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sg_datatables_twig_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('datatable_render', array($this, 'datatableRender'), array('is_safe' => array('all'))),
            new Twig_SimpleFunction('datatable_render_html', array($this, 'datatableRenderHtml'), array('is_safe' => array('all'))),
            new Twig_SimpleFunction('datatable_render_js', array($this, 'datatableRenderJs'), array('is_safe' => array('all'))),
            new Twig_SimpleFunction('datatable_filter_render', array($this, 'datatableFilterRender'), array('is_safe' => array('all'), 'needs_environment' => true)),
            new Twig_SimpleFunction('datatable_icon', array($this, 'datatableIcon'), array('is_safe' => array('all'), 'needs_environment' => true))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('length_join', array($this, 'lengthJoin'))
        );
    }

    //-------------------------------------------------
    // Functions && Filters
    //-------------------------------------------------

    /**
     * Creates the lengthMenu parameter.
     *
     * @param array $values
     *
     * @throws Exception
     * @return string
     */
    public function lengthJoin(array $values)
    {
        $result = '[' . implode(', ', $values) . ']';

        if (in_array(-1, $values, true)) {
            $translation = $this->translator->trans('datatables.datatable.all');
            $count = count($values) - 1;

            if (-1 !== $values[$count]) {
                throw new Exception('lengthJoin(): For lengthMenu the value -1 should always be the last one.');
            }

            $result = '[[' . implode(', ', $values) . '],' . '[';
            $values[$count] = '"' . $translation . '"';
            $result .= implode(', ', $values);
            $result .= ']]';
        }

        return $result;
    }

    /**
     * Renders the template.
     *
     * @param AbstractDatatableView $datatable
     *
     * @return mixed|string|void
     * @throws Exception
     */
    public function datatableRender(AbstractDatatableView $datatable)
    {
        return $datatable->render();
    }

    /**
     * Renders the custom datatable filter.
     *
     * @param Twig_Environment      $twig
     * @param AbstractDatatableView $datatable
     * @param AbstractColumn        $column
     * @param integer               $loopIndex
     *
     * @return mixed|string|void
     */
    public function datatableFilterRender(Twig_Environment $twig, AbstractDatatableView $datatable, AbstractColumn $column, $loopIndex)
    {
        if ($filterProperty = $column->getFilter()->getProperty()) {
            $filterColumnId = $datatable->getColumnIdByColumnName($filterProperty);
        } else {
            $filterColumnId = $loopIndex;
        }

        return $twig->render($column->getFilter()->getTemplate(), array(
            'column' => $column,
            'filterColumnId' => $filterColumnId,
            'tableId' => $datatable->getName()
            )
        );
    }

    /**
     * Renders the html template.
     *
     * @param AbstractDatatableView $datatable
     *
     * @return mixed|string|void
     * @throws Exception
     */
    public function datatableRenderHtml(AbstractDatatableView $datatable)
    {
        return $datatable->render('html');
    }

    /**
     * Renders the js template.
     *
     * @param AbstractDatatableView $datatable
     *
     * @return mixed|string|void
     * @throws Exception
     */
    public function datatableRenderJs(AbstractDatatableView $datatable)
    {
        return $datatable->render('js');
    }

    /**
     * Renders icon && label.
     *
     * @param Twig_Environment $twig
     * @param string           $icon
     * @param string           $label
     *
     * @return string
     */
    public function datatableIcon(Twig_Environment $twig, $icon, $label = '')
    {
        if ($icon)
            return $twig->render('SgDatatablesBundle:Action:icon.html.twig', array('icon' => $icon, 'label' => $label));
        else
            return $label;
    }
}
