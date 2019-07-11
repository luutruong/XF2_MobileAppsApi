<?php

namespace Truonglv\Api\BbCode\Renderer;

use XF\Entity\Attachment;
use Truonglv\Api\XF\Str\Formatter;
use Truonglv\Api\XF\Str\EmojiFormatter;

class SimpleHtml extends \XF\BbCode\Renderer\SimpleHtml
{
    public function addTag($tag, array $config)
    {
        if (!in_array($tag, $this->getWhitelistTags(), true)) {
            unset($config['callback']);
        }

        parent::addTag($tag, $config);
    }

    public function renderTagUrl(array $children, $option, array $tag, array $options)
    {
        $options = array_replace($options, [
            'unfurl' => false,
            'allowUnfurl' => false
        ]);

        return parent::renderTagUrl($children, $option, $tag, $options);
    }

    public function renderTagAttach(array $children, $option, array $tag, array $options)
    {
        $id = intval($this->renderSubTreePlain($children));
        if ($id > 0) {
            $attachments = $options['attachments'];

            if (!empty($attachments[$id])) {
                /** @var Attachment $attachmentRef */
                $attachmentRef = $attachments[$id];
                $params = [
                    'id' => $id,
                    'attachment' => $attachmentRef,
                    'canView' => true,
                    'full' => $this->isFullAttachView($option),
                    'styleAttr' => $this->getAttachStyleAttr($option),
                    'alt' => $this->getImageAltText($option) ?: ($attachmentRef ? $attachmentRef->filename : ''),
                    'noLightbox' => true,
                    'tApiViewUrl' => $this->getAttachmentViewUrl($attachmentRef)
                ];

                $rendered = $this->templater->renderTemplate('public:bb_code_tag_attach', $params);
                $rendered = trim(strval($rendered));

                if (substr($rendered, 0, 4) === '<img') {
                    if (strpos($rendered, 'data-width') === false) {
                        $rendered = substr($rendered, 0, 4)
                            . ' data-width="' . $attachmentRef->Data->width . '"'
                            . substr($rendered, 4);
                    }
                    if (strpos($rendered, 'data-height') === false) {
                        $rendered = substr($rendered, 0, 4)
                            . ' data-height="' . $attachmentRef->Data->height . '"'
                            . substr($rendered, 4);
                    }
                }

                return $rendered;
            }
        }

        return parent::renderTagAttach($children, $option, $tag, $options);
    }

    public function filterString($string, array $options)
    {
        /** @var Formatter $formatter */
        $formatter = $this->formatter;
        $formatter->setTApiDisableSmilieWithSpriteParams(true);

        /** @var EmojiFormatter $emojiFormatter */
        $emojiFormatter = $formatter->getEmojiFormatter();
        $emojiFormatter->setTApiDisableFormatToImage(true);

        return parent::filterString($string, $options);
    }

    protected function getWhitelistTags()
    {
        return [
            'attach',
            'left',
            'center',
            'right',
            'url',
            'font',
            'size',
            'img',
            'user',
            'plain',
            'code',
            'quote',
            'b',
            'u',
            'i',
            's',
            'color',
            'icode',
            'list',
            'code'
        ];
    }

    protected function getAttachmentViewUrl(Attachment $attachment)
    {
        /** @var \XF\Api\App $app */
        $app = \XF::app();
        $token = null;
        $apiKey = $app->request()->getApiKey();

        if ($attachment->has_thumbnail) {
            $token = \XF::$time . '.' . md5(
                \XF::$time
                . $apiKey
                . $attachment->attachment_id
                . $app->config('globalSalt')
            );
        }

        return $app->router('public')
            ->buildLink('full:attachments', $attachment, [
                'hash' => $attachment->temp_hash ?: null,
                'tapi_token' => $token
            ]);
    }
}
