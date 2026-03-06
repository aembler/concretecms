<?php

namespace Concrete\Block\Content;

use Concrete\Core\Application\UserInterface\Icon\IconInterface;
use Concrete\Core\Application\UserInterface\Icon\InlineSvgIcon;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Block\BlockType\Editor\AbstractEditor;
use Concrete\Core\Block\BlockType\Editor\EditorInterface;
use Concrete\Core\Block\ProvidesEditorInterface;
use Concrete\Core\Block\ProvidesIconInterface;
use Concrete\Core\Editor\LinkAbstractor;
use Concrete\Core\Feature\Features;
use Concrete\Core\Feature\UsesFeatureInterface;
use Concrete\Core\File\Tracker\FileTrackableInterface;
use Concrete\Core\File\Tracker\RichTextExtractor;

/**
 * The controller for the content block.
 *
 * @package Blocks
 * @subpackage Content
 *
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2022 concreteCMS. (http://www.concretecms.org)
 * @license    http://www.concretecms.org/license/     MIT License
 */
class Controller extends BlockController implements FileTrackableInterface, UsesFeatureInterface, ProvidesEditorInterface, ProvidesIconInterface
{
    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    protected $btTable = 'btContentLocal';

    /**
     * @var int
     */
    protected $btInterfaceWidth = 600;

    /**
     * @var int
     */
    protected $btInterfaceHeight = 465;

    /**
     * @var bool
     */
    protected $btCacheBlockRecord = true;

    /**
     * @var bool
     */
    protected $btCacheBlockOutput = true;

    /**
     * @var bool
     */
    protected $btCacheBlockOutputOnPost = true;

    /**
     * @var bool
     */
    protected $btCacheBlockOutputForRegisteredUsers = null;

    /**
     * @var bool
     */
    protected $btCacheBlockOutputOnEditMode = false;

    /**
     * @var int
     */
    protected $btCacheBlockOutputLifetime = 0; //until manually updated or cleared

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btExportContentColumns
     */
    protected $btExportContentColumns = ['content'];

    public function getEditor(string $mode): ?EditorInterface
    {
        $content = $mode === 'edit' ? $this->getContentEditMode() : '';

        return new class($content) extends AbstractEditor {
            public function __construct(protected string $content)
            {
            }

            public function getComponent(): string
            {
                return 'ConcreteBlockContentEditor';
            }

            public function getComponentProps(): array
            {
                return [
                    'content' => $this->content,
                ];
            }
        };
    }

    /**
     * {@inhertdoc}.
     */
    public function getRequiredFeatures(): array
    {
        return [
            Features::IMAGERY,
        ];
    }

    /**
     * @return string
     */
    public function getBlockTypeDescription()
    {
        return t('HTML/WYSIWYG Editor Content.');
    }

    /**
     * @return string
     */
    public function getBlockTypeName()
    {
        return t('Content');
    }

    public function getIcon(): IconInterface
    {
        return new InlineSvgIcon('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 5.75A1.75 1.75 0 0 1 7.75 4h8.5A1.75 1.75 0 0 1 18 5.75v12.5A1.75 1.75 0 0 1 16.25 20h-8.5A1.75 1.75 0 0 1 6 18.25V5.75Z" stroke="currentColor" stroke-width="1.5"/><path d="M9 9.25h6M9 12h6M9 14.75h3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>');
    }

    public function cacheBlockOutputForRegisteredUsers()
    {
        if ($this->btCacheBlockOutputForRegisteredUsers === null) {
            $this->btCacheBlockOutputForRegisteredUsers = strrpos($this->content, 'data-scs') === false;
        }

        return $this->btCacheBlockOutputForRegisteredUsers;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return LinkAbstractor::translateFrom($this->content);
    }

    /**
     * @return string
     */
    public function getSearchableContent()
    {
        return $this->content;
    }

    /**
     * @param string $str
     *
     * @return array|string|string[]
     */
    public function br2nl($str)
    {
        return str_replace(["\r\n", "<br />\n", "<br />\r\n"], "\n", $str);
    }

    /**
     * @return void
     */
    public function view()
    {
        $this->set('content', $this->getContent());
    }

    /**
     * @return string
     */
    public function getContentEditMode()
    {
        return LinkAbstractor::translateFromEditMode($this->content);
    }

    /**
     * @param array<string,string> $args
     */
    public function save($args)
    {
        if (isset($args['content'])) {
            $args['content'] = LinkAbstractor::translateTo($args['content']);
        }
        parent::save($args);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\File\Tracker\FileTrackableInterface::getUsedFiles()
     */
    public function getUsedFiles()
    {
        return $this->app->make(RichTextExtractor::class)->extractFiles($this->content);
    }

    /**
     * @deprecated use \Concrete\Core\File\Tracker\RichTextExtractor
     */
    protected function getUsedFilesImages()
    {
        $files = [];
        $matches = [];
        if ($this->content && preg_match_all('/\<concrete-picture[^>]*?fID\s*=\s*[\'"]([^\'"]*?)[\'"]/i', $this->content, $matches)) {
            list(, $ids) = $matches;
            foreach ($ids as $id) {
                $files[] = $id;
            }
        }

        return $files;
    }

    /**
     * @deprecated use \Concrete\Core\File\Tracker\RichTextExtractor
     */
    protected function getUsedFilesDownload()
    {
        if (!$this->content) {
            return [];
        }
        preg_match_all('(FID_DL_\d+)', $this->content, $matches);

        return array_map(
            function ($match) {
                return explode('_', $match)[2];
            },
            $matches[0]
        );
    }
}
