<?php

namespace Truonglv\Api\XF\Entity;

use Truonglv\Api\App;

class ProfilePost extends XFCP_ProfilePost
{
    /**
     * @param \XF\Api\Result\EntityResult $result
     * @param int $verbosity
     * @param array $options
     * @return void
     */
    protected function setupApiResultData(
        \XF\Api\Result\EntityResult $result,
        $verbosity = \XF\Entity\ProfilePost::VERBOSITY_NORMAL,
        array $options = []
    ) {
        parent::setupApiResultData($result, $verbosity, $options);

        App::attachReactions($result, $this);
        $stringFormatter = $this->app()->stringFormatter();
        $plainText = $stringFormatter->stripBbCode($this->message, [
            'stripQuote' => true
        ]);

        $result->tapi_message_plain_text = $plainText;
        $result->tapi_message_plain_text_preview = $stringFormatter->wholeWordTrim(
            $plainText,
            $this->app()->options()->tApi_discussionPreviewLength
        );

        $visitor = \XF::visitor();
        if ($visitor->user_id > 0) {
            $result->can_comment = $this->canComment();
            $result->can_report = $this->canReport();

            $result->can_ignore = $this->User !== null && $visitor->canIgnoreUser($this->User);
            $result->is_ignored = $visitor->isIgnoring($this->user_id);
        } else {
            $result->can_comment = false;
            $result->can_report = false;
            $result->can_ignore = false;
            $result->is_ignored = false;
        }

        $result->view_url = $this->app()->router('public')
            ->buildLink('canonical:profile-posts', $this);
    }
}
