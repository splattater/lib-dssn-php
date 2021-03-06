<?php
/**
 * Activity object factory
 *
 * @author  {@link http://sebastian.tramp.name Sebastian Tramp}
 * @license http://sam.zoy.org/wtfpl/  Do What The Fuck You Want To Public License (WTFPL)
 */
class DSSN_Activity_Object_Factory
{
    /*
     * uses the content of //activity:object/activity:objectType/text()
     * and returns an appropriate object
     */
    public static function newFromObjectTypeString($text = null)
    {
        if ($text == null) {
            $message = 'newFromObjectTypeString needs an input text';
            throw new DSSN_Exception($message);
        }

        switch ($text) {
            case 'note':
            case 'http://activitystrea.ms/schema/1.0/note':
                return new DSSN_Activity_Object_Note();
                break;

            case 'bookmark':
            case 'http://activitystrea.ms/schema/1.0/bookmark':
                return new DSSN_Activity_Object_Bookmark();
                break;

            case 'photo':
            case 'http://activitystrea.ms/schema/1.0/photo':
                return new DSSN_Activity_Object_Photo();
                break;

            case 'inReplyTo':
            case 'http://activitystrea.ms/schema/1.0/inReplyTo':
                return new DSSN_Activity_Context();
                break;

            default:
                $object = new DSSN_Activity_Object();
                $object->setType($text);
                return $object;
        }
    }

    /*
     * uses the content of //activity:object and returns an appropriate
     * verb object
     */
    public static function newFromDOMNode(DOMNode $node)
    {
        // create xpath environment
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->appendChild($dom->importNode($node, true));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('atom', DSSN_ATOM_NS);
        $xpath->registerNamespace('activity', DSSN_ACTIVITIES_NS);

        // fetch object-type and create object of that
        $nodes = $xpath->query('/activity:object/activity:object-type/text()');
        $object = null;
        foreach($nodes as $node) {
            $object = DSSN_Activity_Object_Factory::newFromObjectTypeString(strip_tags($node->wholeText));
        }

        // fetch name
        $nodes = $xpath->query('/activity:object/atom:title/text()');
        foreach ($nodes as $node) {
            $object->setName(strip_tags($node->wholeText));

            if ($object instanceof DSSN_Activity_Object_Note) {
                $object->setContent(strip_tags($node->wholeText));
            }
        }

        // fetch iri
        $nodes = $xpath->query('/activity:object/atom:id/text()');
        foreach ($nodes as $node) {
            $object->setIri(strip_tags($node->wholeText));
        }

        return $object;
    }

}

