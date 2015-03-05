<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_EmbeddedAuthorizationList($params, &$smarty)
{
    global $current_company, $current_user;

    $object_type_id = $params['object_type_id'];
    $object_id = $params['object_id'];

    $ulf = new UserListFactory();

    $hlf = new HierarchyListFactory();
    $hotlf = new HierarchyObjectTypeListFactory();

    $alf = new AuthorizationListFactory();
    $alf->setObjectType($object_type_id);
    $tmp_authorizing_obj = ( is_object( $alf->getObjectHandler() ) ) ? $alf->getObjectHandler()->getById( $object_id ) : FALSE;
    if ( is_object($tmp_authorizing_obj) ) {
        $authorizing_obj = $tmp_authorizing_obj->getCurrent();
    } else {
        return FALSE;
    }
    unset($alf);

    $user_id = $authorizing_obj->getUserObject()->getId();

    $alf = new AuthorizationListFactory();
    $alf->getByObjectTypeAndObjectId($object_type_id, $object_id);
    if ( $alf->getRecordCount() > 0 ) {
        foreach( $alf as $authorization_obj) {
            $authorization_data[] = array(
                        'id' => $authorization_obj->getId(),
                        'created_by_full_name' => $ulf->getById( $authorization_obj->getCreatedBy() )->getCurrent()->getFullName(),
                        'authorized' => $authorization_obj->getAuthorized(),
                        'created_date' => $authorization_obj->getCreatedDate(),
                        'created_by' => $authorization_obj->getCreatedBy(),
                        'updated_date' => $authorization_obj->getUpdatedDate(),
                        'updated_by' => $authorization_obj->getUpdatedBy(),
                        'deleted_date' => $authorization_obj->getDeletedDate(),
                        'deleted_by' => $authorization_obj->getDeletedBy()
                    );
            $user_id = $authorization_obj->getCreatedBy();
        }
    }

    if ( isset($authorization_obj) AND $authorizing_obj->getStatus() == 30 ) {
        //If the object is still pending authorization, display who its waiting on...
        $hierarchy_id = $hotlf->getByCompanyIdAndObjectTypeId( $current_company->getId(), $object_type_id )->getCurrent()->getHierarchyControl();
        Debug::Text('Hierarchy ID: '. $hierarchy_id, __FILE__, __LINE__, __METHOD__,10);

        //Get Parents
        $parent_level_user_ids = $hlf->getParentLevelIdArrayByHierarchyControlIdAndUserId($hierarchy_id, $user_id );
        //Debug::Arr( $parent_level_user_ids, 'Parent Level Ids', __FILE__, __LINE__, __METHOD__,10);

        if ( $parent_level_user_ids !== FALSE AND count($parent_level_user_ids) > 0 ) {
            Debug::Text('Adding Pending Line: ', __FILE__, __LINE__, __METHOD__,10);

            foreach($parent_level_user_ids as $parent_user_id ) {
                $created_by_full_name[] = $ulf->getById( $parent_user_id )->getCurrent()->getFullName();
            }

            $authorization_data[] = array(
                        'id' => NULL,
                        'created_by_full_name' => implode('<br>', $created_by_full_name),
                        'authorized' => NULL,
                        'created_date' => NULL,
                        'created_by' => NULL
                    );

        }
    }

    $smarty->assign_by_ref('authorization_data', $authorization_data);
    $smarty->display('authorization/EmbeddedAuthorizationList.tpl');
}

/* vim: set expandtab: */

?>
