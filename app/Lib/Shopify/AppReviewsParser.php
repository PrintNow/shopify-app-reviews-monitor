<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace App\Lib\Shopify;

use DOMXPath;
use DateTime;
use DOMDocument;
use RuntimeException;
use Illuminate\Support\Str;
use App\Lib\PHPSpider\Selector;

class AppReviewsParser
{
    public string $html;
    public string $slug;

    /** @var string $_selectedReviewHtml 当前选中 Review DOM 的 HTML */
    private string $_selectedReviewHtml;

    /** @var int $reviewCount 评价数量 */
    public int $reviewCount = 0;

    public function __construct(string $html, string $slug)
    {
        $selectReviews = Selector::select(
            $html,
            "//div[contains(@class,'lg:tw-hidden tw-text-body-md tw-text-fg-tertiary tw-pb-md tw-mb-md')]"
        );
        $this->reviewCount = $selectReviews === null ? 0 : (int)preg_replace('/\D/', '', $selectReviews);

        $this->html = trim(Selector::select($html, "//section[@id='adp-reviews']"));
        $this->slug = $slug;
    }

    /**
     * 获取 Review 数据
     *
     * @return array{
     *     rid: int,
     *     rating: int,
     *     name: string,
     *     content: string,
     *     edited_at: int,
     *     posted_at: int
     * }[]
     *
     * @throws \Exception
     */
    public function getReviewsData(): array
    {
        $data = [];
        foreach ($this->getRIDs() as $RID) {
            $this->_selectedReviewHtml = Selector::select(
                $this->html,
                "//div[@data-review-content-id='{$RID}']",
            );
            $date = $this->getDate();
            $data[] = [
                'rid' => $RID,
                'slug' => $this->slug,
                'name' => $this->getName(),
                'rating' => $this->getRating(),
                'comment' => $this->getComment(),
                'edited_at' => $date['edited_at'],
                'posted_at' => $date['posted_at'],
            ];
        }

        return $data;
    }

    /**
     * 获取评价总数量
     *
     * @return float|int
     */
    public function getReviewCount(): int
    {
        return $this->reviewCount;
    }

    ###################################################
    #####################数据清洗#######################
    ###################################################

    /**
     * 获取评价内容
     *
     * @return string
     */
    private function getComment(): string
    {
        $selector = 'div[data-truncate-content-copy] > p';
        $value = Selector::select($this->_selectedReviewHtml, $selector, 'css');

        $splitText = explode("<br/>", is_array($value) ? implode("\n\n", $value) : $value);

        foreach ($splitText as &$item) {
            $item = html_entity_decode(trim($item));
        }

        return implode("\n", $splitText);
    }

    /**
     * @return int
     * @throws \Exception
     */
    private function getRating(): int
    {
        $doc = new DOMDocument();
        $doc->loadHTML($this->_selectedReviewHtml);

        $xpath = new DOMXpath($doc);
        $res = $xpath->query("//div[@role='img']");

        if ($res->item(0) === null) {
            throw new RuntimeException("评分获取失败");
        }

        $attribute = $this->trimText($res->item(0)->getAttribute('aria-label'));

        return (int)$attribute[0];
    }

    /**
     * 获取名字
     *
     * @return string
     */
    private function getName(): string
    {
        $name = $this->trimText(Selector::select(
            $this->_selectedReviewHtml,
            "//div[contains(@class,'tw-text-fg-tertiary tw-text-body-xs')][1]",
        ));

        return html_entity_decode($name);
    }

    /**
     * 获取日期
     *
     * @return array{posted_at: int, edited_at: int}
     */
    private function getDate(): array
    {
        $date = $this->trimText(Selector::select(
            $this->_selectedReviewHtml,
            "//div[contains(@class,'tw-text-body-xs tw-text-fg-tertiary')][1]",
        ));

        $res = [
            'posted_at' => null,
            'edited_at' => null,
        ];

        $dateTime = DateTime::createFromFormat('F d, Y', trim(str_replace('Edited', '', $date)))
            ->setTime(0, 0)
            ->format('Y-m-d');

        if (Str::contains($date, 'Edited')) {
            $res['edited_at'] = $dateTime;
        } else {
            $res['posted_at'] = $dateTime;
        }

        return $res;
    }

    ###################################################
    ######################工具函数######################
    ###################################################

    public function trimText($value)
    {
        return preg_replace(["/\r/", "/\n/", "/\t/"], "", trim($value));
    }

    /**
     * 拿到 reviews ID
     *
     * @return int[]
     */
    public function getRIDs(): array
    {
        $doc = new DOMDocument();
        $doc->loadHTML($this->html);

        $xpath = new DOMXpath($doc);
        $res = $xpath->query('//div[@data-merchant-review]');

        $rid = [];
        foreach ($res as $element) {
            $rid[] = (int)$element->attributes->getNamedItem("data-review-content-id")->nodeValue;
        }

        return $rid;
    }
}
