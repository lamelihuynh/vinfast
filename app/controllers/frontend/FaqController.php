<?php
/**
 * app/controllers/frontend/FaqController.php
 * Owner: Nhat Linh (Member 2)
 * Routes: GET /faq
 *
 *  * FAQ page rendered as a Bootstrap accordion.
 * Entries come from the faqs table managed by FaqAdminController.
 */
class FaqController {
    
    /**
     * GET /faq
     * Displays all active FAQ entries in an accordion
     */
    public function index(): void
{
    $faqs = Faq::all();

    $faq_groups = [];

    foreach ($faqs as $faq) {

        $slug = $faq['topic_slug'];

        if (!isset($faq_groups[$slug])) {
            $faq_groups[$slug] = [
                'id' => $slug,
                'icon' => $faq['icon_svg'] ?: '',
                'label' => $faq['topic_name'],
                'questions' => []
            ];
        }

        $faq_groups[$slug]['questions'][] = [
            'q' => $faq['question'],
            'a' => $faq['answer']
        ];
    }

    $faq_groups = array_values($faq_groups);

    SEO::set(
        'Hỏi & Đáp',
        'Câu hỏi thường gặp về VinFast'
    );

    View::render('frontend/faq/index', [
        'faq_groups' => $faq_groups
    ]);
}
}