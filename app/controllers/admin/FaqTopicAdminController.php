<?php

class FaqTopicAdminController {

    public function index(): void {

        $topics = FaqTopic::all();

        SEO::set('Quản lý chủ đề FAQ');

        View::render('admin/faq-topic/index', [
            'topics' => $topics
        ], 'admin');
    }

    public function create(): void {

        View::render('admin/faq-topic/form', [
            'topic' => null,
            'action' => 'create'
        ], 'admin');
    }

    public function edit(int $id): void {

        $topic = FaqTopic::getById($id);

        if (!$topic) {
            header('Location: ' . ADMIN_URL . 'faq-topic');
            exit;
        }

        View::render('admin/faq-topic/form', [
            'topic' => $topic,
            'action' => 'edit'
        ], 'admin');
    }

    public function save(): void {

        Auth::verifyCsrf($_POST['_csrf']);

        $id = (int)($_POST['id'] ?? 0);

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $iconSvg = trim($_POST['icon_svg'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        $isActive = isset($_POST['is_active']);

        if ($id > 0) {

            FaqTopic::update(
                $id,
                $name,
                $slug,
                $iconSvg,
                $sortOrder,
                $isActive
            );

        } else {

            FaqTopic::create(
                $name,
                $slug,
                $iconSvg,
                $sortOrder,
                $isActive
            );
        }

        header('Location: ' . ADMIN_URL . 'faq-topic');
        exit;
    }

    public function delete(int $id): void {

        Auth::verifyCsrf($_POST['_csrf']);

        FaqTopic::delete($id);

        header('Location: ' . ADMIN_URL . 'faq-topic');
        exit;
    }
}