<?php

/**
 * Trait mit Hilfsfunktionen für den Datenaustausch.
 */
trait InstanceHelper
{
    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        switch ($Message) :
            case IPS_KERNELMESSAGE:
                if ($Data[0] != KR_READY) {
                    break;
                }
            case IM_DISCONNECT:
            case IPS_KERNELSTARTED:
                $this->RegisterParent();
                if ($this->HasActiveParent()) {
                    $this->IOChangeState(IS_ACTIVE);
                } else {
                    $this->IOChangeState(IS_INACTIVE);
                }
                break;
            case IM_CHANGESTATUS:
                if ($SenderID == $this->ParentID) {
                    $this->IOChangeState($Data[0]);
                }
                break;
        endswitch;
    }

    /**
     * Ermittelt den Parent und verwaltet die Einträge des Parent im MessageSink
     * Ermöglicht es das Statusänderungen des Parent empfangen werden können.
     *
     * @access protected
     * @return int ID des Parent.
     */
    protected function RegisterParent()
    {
        $OldParentId = $this->ParentID;
        $ParentId = @IPS_GetInstance($this->InstanceID)['ConnectionID'];
        if ($ParentId <> $OldParentId) {
            if ($OldParentId > 0) {
                $this->UnregisterMessage($OldParentId, IM_CHANGESTATUS);
                $this->UnregisterMessage($OldParentId, IM_DISCONNECT);

                if ((float)IPS_GetKernelVersion() < 4.2) {
                    $this->RegisterMessage($OldParentId, IPS_KERNELMESSAGE);
                } else {
                    $this->RegisterMessage($OldParentId, IPS_KERNELSTARTED);
                    $this->RegisterMessage($OldParentId, IPS_KERNELSHUTDOWN);
                }
            }
            if ($ParentId > 0) {
                $this->RegisterMessage($ParentId, IM_CHANGESTATUS);
                $this->UnregisterMessage($ParentId, IM_DISCONNECT);

                if ((float)IPS_GetKernelVersion() < 4.2) {
                    $this->RegisterMessage($ParentId, IPS_KERNELMESSAGE);
                } else {
                    $this->RegisterMessage($ParentId, IPS_KERNELSTARTED);
                    $this->RegisterMessage($ParentId, IPS_KERNELSHUTDOWN);
                }
            } else {
                $ParentId = 0;
            }
            $this->ParentID = $ParentId;
        }

        return $ParentId;
    }

    /**
     * Prüft den Parent auf vorhandensein und Status.
     *
     * @access protected
     * @return bool True wenn Parent vorhanden und in Status 102, sonst false.
     */
    protected function HasActiveParent()
    {
        $instance = @IPS_GetInstance($this->InstanceID);
        if ($instance['ConnectionID'] > 0) {
            $parent = IPS_GetInstance($instance['ConnectionID']);
            if ($parent['InstanceStatus'] == 102) {
                return true;
            }
        }

        return false;
    }

    /**
     * Destroy instance by guid and identifier
     * @param null $guid
     * @param null $Ident
     * @return bool
     */
    protected function DestroyInstanceByModuleAndIdent($guid = NULL, $Ident = NULL)
    {
        // get module instances
        $instances = IPS_GetInstanceListByModuleID($guid);

        // search for instance with ident
        foreach ($instances AS $instance_id) {
            $instance = IPS_GetObject($instance_id);
            if ($instance['ObjectIdent'] == $Ident) {
                // delete instance
                IPS_DeleteInstance($instance_id);
                return true;
            }
        }

        return false;
    }
}