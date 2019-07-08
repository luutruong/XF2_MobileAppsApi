<?php

namespace Truonglv\Api\XF\Entity;

use Truonglv\Api\App;

class ConversationMessage extends XFCP_ConversationMessage
{
    protected function setupApiResultData(
        \XF\Api\Result\EntityResult $result,
        $verbosity = \XF\Entity\ConversationMessage::VERBOSITY_NORMAL,
        array $options = []
    ) {
        parent::setupApiResultData($result, $verbosity, $options);

        App::includeMessageHtmlIfNeeded($result, $this);
        $result->tapi_is_visitor_message = (\XF::visitor()->user_id === $this->user_id);
    }
}