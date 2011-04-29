<?php
/**
 * A DSSN Resource object
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
abstract class DSSN_Resource
{
    private $iri = null;

    public $namespaces = array();
   
    public function __construct($iri = null) {
        DSSN_Utils::setConstants();

        if ($iri != null) {
            $this->iri = (string) $iri;
        }

        $this->namespaces = array(
            'aair' => DSSN_AAIR_NS,
            'rdf'  => DSSN_RDF_NS,
            'rdfs' => DSSN_RDFS_NS,
            'foaf' => DSSN_FOAF_NS,
            'atom' => DSSN_ATOM_NS,
            'xsd'  => DSSN_XSD_NS,
        );

    }

    /*
     * return the URI string of the RDF type of the resource
     */
    public function getType()
    {
        switch (get_class($this)) {
            case 'DSSN_Activity_Verb_Post':    return DSSN_AAIR_NS . 'Verb';
            case 'DSSN_Activity_Verb_Share':   return DSSN_AAIR_NS . 'Share';
            case 'DSSN_Activity_Object_Note':  return DSSN_AAIR_NS . 'Note';
            case 'DSSN_Activity_Actor_User':   return DSSN_AAIR_NS . 'User';
            case 'DSSN_Activity':              return DSSN_AAIR_NS . 'Activity';
            default:
                throw new Exception('Unknown class type IRI of object class '
                    . get_class($this) . '. Please add it to DSSN_Resource::getType.');
                exit;
        }
    }

    public function __toString()
    {
        return (string) $this->getIri();
    }

    /**
     * Generate iri.
     *
     * @return iri.
     */
    public function generateIri()
    {
        $base      = 'http://example.org/Activities/';
        $hash      = md5(date('c',time()) . get_class($this));
        $iri       = $base . $hash;
        $this->iri = $iri;
    }

    /**
     * Get iri.
     *
     * @return iri.
     */
    public function getIri()
    {
        if ($this->iri == null) {
            $this->generateIri();
        }
        return $this->iri;
    }

    /**
     * Set iri.
     *
     * @param iri the value to set.
     */
    public function setIri($iri)
    {
        $this->iri = (string) $iri;
    }

    /*
     * returns the ARC2 Turtle Template for the resource as string
     */
    public function getTurtleTemplate()
    {
        /* default template only a rdf:type statement */
        $template  = <<<EndOfTemplate
            ?resource rdf:type ?type .
EndOfTemplate;
        return $template;
    }

    /*
     * returns the variable values for the ARC2 Turtle template as assoc. array
     */
    public function getTurtleTemplateVars()
    {
        $vars             = array();
        $vars['resource'] = $this->getIri();
        $vars['type']     = $this->getType();
        return $vars;
    }

    /*
     * exports the activity as RDF using turtle templates
     */
    public function toRDF()
    {
        // https://github.com/semsol/arc2/wiki/Turtle-Templates
        require_once('ARC2/ARC2.php');
        $arc2obj = ARC2::getResource( array('ns' => $this->namespaces) ); /* any component will do */

        /* generate the index from the object template and the variables */
        $template = $this->getTurtleTemplate();
        $vars     = $this->getTurtleTemplateVars();
        $index    = $arc2obj->getFilledTemplate($template, $vars);

        /* merge indizes of all subresources */
        foreach ($this->getSubResources() as $resource) {
            if ($resource instanceof DSSN_Resource) {
                $resourceIndex = $resource->toRDF();
                $index = ARC2::getMergedIndex($index, $resourceIndex);
            }
        }
        
        //$turtle = $obj->toTurtle($index);
        return $index;
    }

    /*
     * returns an array of subresource objects (to merge with main toRDF index)
     */
    public function getSubResources()
    {
        return array();
    }

}