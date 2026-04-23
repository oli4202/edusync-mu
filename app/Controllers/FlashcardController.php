<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Flashcard;
use App\Models\Subject;
use App\Models\User;
use function App\redirect;

class FlashcardController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        $currentDeck = $_GET['deck'] ?? null;
        $cardList = Flashcard::findByUser($userId, $currentDeck);
        $deckList = Flashcard::getDecks($userId);
        $totalCount = Flashcard::getCount($userId);
        $subjects = Subject::findByUser($userId);
        
        $deckNames = array_map(fn($deck) => $deck['deck_name'], $deckList);

        $this->render('pages/flashcards', compact(
            'user', 'userId', 'currentDeck', 'cardList', 
            'deckList', 'totalCount', 'subjects', 'deckNames'
        ));
    }

    public function add(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Flashcard::create($this->session->userId(), $_POST);
        }
        $deck = $_GET['deck'] ?? '';
        redirect('/flashcards' . ($deck ? '?deck=' . urlencode($deck) : ''));
    }

    public function delete(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Flashcard::delete((int)$_POST['card_id'], $this->session->userId());
        }
        $deck = $_GET['deck'] ?? '';
        redirect('/flashcards' . ($deck ? '?deck=' . urlencode($deck) : ''));
    }

    public function generate(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Flashcard::generateAI(
                $this->session->userId(),
                $_POST['topic'] ?? '',
                (int)($_POST['count'] ?? 5),
                (int)($_POST['subject_id'] ?? 0),
                $_POST['deck_name'] ?? null
            );
        }
        redirect('/flashcards');
    }
}
