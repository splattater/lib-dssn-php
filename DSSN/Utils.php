<?php
/**
 * DSSN Utils
 *
 * @author  {@link http://sebastian.tramp.name Sebastian Tramp}
 * @license http://sam.zoy.org/wtfpl/  Do What The Fuck You Want To Public License (WTFPL)
 */
class DSSN_Utils
{

    /*
     * takes an ARC2 index / rdfphp array and adds a triple based on the result
     * of an extended SPARQL query
     */
    static public function indexAddTripleFromExtendedFormat
            (array $index, array $s, array $p, array $o) {
        $model = new DSSN_Model($index);


        $typeO = $o['type'];
        $object = array();
        $object['value'] = $o['value'];
        switch ($typeO) {
            case 'uri':
                $object['type'] = 'uri';
                break;
            case 'typed-literal':
                $object['type'] = 'literal';
                $object['datatype'] = $o['datatype'];
                break;
            case 'literal':
                $object['type'] = 'literal';
                if (isset($o['xml:lang'])) {
                    $object['lang'] = $o['xml:lang'];
                }
                break;
            default:
                /* be quiet here */
                break;
        }

        $statement = array();
        $s = $s['value']; // is always an IRI (or bnode)
        $p = $p['value']; // is always an IRI
        $pArray[$p] = $object;
        $statement[$s] = $pArray;
        $model->addStatements($statement);

        return $model->getStatements();
    }

    /*
     * set the needed constants for all DSSN classes
     *
     * @return null
     */
    static public function setConstants() {
        if (defined('DSSN_AAIR_NS')) {
            return;
        } else {
            /*
             * DSSN namespace constants
             */
            define('DSSN_AAIR_NS', 'http://xmlns.notu.be/aair#');
            define('DSSN_RDF_NS', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
            define('DSSN_RDFS_NS', 'http://www.w3.org/2000/01/rdf-schema#');
            define('DSSN_FOAF_NS', 'http://xmlns.com/foaf/0.1/');
            define('DSSN_ATOM_NS', 'http://www.w3.org/2005/Atom');
            define('DSSN_XSD_NS', 'http://www.w3.org/2001/XMLSchema#');
            define('DSSN_ACTIVITIES_NS', 'http://activitystrea.ms/spec/1.0/');

            /*
             * DSSN resource constants
             */
            define('DSSN_ATOM_published', DSSN_ATOM_NS . '/published');

            define('DSSN_AAIR_Activity', DSSN_AAIR_NS . 'Activity');
            define('DSSN_AAIR_activityActor', DSSN_AAIR_NS . 'activityActor');
            define('DSSN_AAIR_activityVerb', DSSN_AAIR_NS . 'activityVerb');
            define('DSSN_AAIR_activityObject', DSSN_AAIR_NS . 'activityObject');
            define('DSSN_AAIR_activityContext', DSSN_AAIR_NS . 'activityContext');
            define('DSSN_AAIR_avatar', DSSN_AAIR_NS . 'avatar');
            define('DSSN_AAIR_content', DSSN_AAIR_NS . 'content');
            define('DSSN_AAIR_name', DSSN_AAIR_NS . 'name');
            define('DSSN_AAIR_thumbnail', DSSN_AAIR_NS . 'thumbnail');

            define('DSSN_RDF_type',     DSSN_RDF_NS . 'type');
            define('DSSN_RDFS_label',   DSSN_RDFS_NS . 'label');
            define('DSSN_FOAF_knows',   DSSN_FOAF_NS . 'knows');
            define('DSSN_FOAF_Person',  DSSN_FOAF_NS . 'Person');
            define('DSSN_FOAF_name',    DSSN_FOAF_NS . 'name');
            define('DSSN_FOAF_firstname',DSSN_FOAF_NS . 'firstname');
            define('DSSN_FOAF_nick' ,   DSSN_FOAF_NS . 'nick');
            define('DSSN_FOAF_surname' ,DSSN_FOAF_NS . 'surname');
            define('DSSN_FOAF_familyName', DSSN_FOAF_NS . 'familyName');
            define('DSSN_FOAF_family_name', DSSN_FOAF_NS . 'family_name');
            define('DSSN_FOAF_depiction',DSSN_FOAF_NS . 'depiction');
            define('DSSN_FOAF_img',     DSSN_FOAF_NS . 'img');
            define('DSSN_FOAF_status',     DSSN_FOAF_NS . 'status');
        }
    }

    /*
     * returns an array of used DSSN namespace/prefix tupels
     *
     * @return array
     */
    static public function getNamespaces() {
        DSSN_Utils::setConstants();
        $namespaces = array(
            'aair' => DSSN_AAIR_NS,
            'rdf'  => DSSN_RDF_NS,
            'rdfs' => DSSN_RDFS_NS,
            'foaf' => DSSN_FOAF_NS,
            'atom' => DSSN_ATOM_NS . '/',
            'xsd'  => DSSN_XSD_NS,
        );
        return $namespaces;
    }

    /**
     * Get all values from specific key in a multidimensional array
     * http://de3.php.net/manual/de/function.array-values.php#103905
     *
     * @param $key string
     * @param $arr array
     * @return null|string|array
     */
    static public function array_value_recursive($key, array $arr){
        $val = array();
        array_walk_recursive($arr, function($v, $k) use($key, &$val){
            if($k == $key) array_push($val, $v);
        });
        return count($val) > 1 ? $val : array_pop($val);
    }

    /**
     * register a standard php library autoloader function suitable to autoload
     * available DSSN classes
     * http://www.php.net/manual/en/function.spl-autoload.php#103548
     *
     * @return null
     */
    public static function registerAutoload()
    {
        return spl_autoload_register(array(__CLASS__, 'includeClass'));
    }

    /*
     * unregister the DSSN autoloader
     * http://www.php.net/manual/en/function.spl-autoload.php#103548
     *
     * @return null
     */
    public static function unregisterAutoload()
    {
        return spl_autoload_unregister(array(__CLASS__, 'includeClass'));
    }

    /*
     * the used DSSN __autoload function
     * http://www.php.net/manual/en/function.spl-autoload.php#103548
     *
     * @return null
     */
    public static function includeClass($class)
    {
        $dirPrefix = dirname(__FILE__) . '/../';
        require($dirPrefix . strtr($class, '_\\', '//') . '.php');
    }


}
