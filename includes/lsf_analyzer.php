<?php
function lsf_analyze_message($text) {
    global $conn;

    $original = $text;
    $lower = mb_strtolower($text, 'UTF-8');

    // Normalizimi i tekstit
    $normalized = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $lower);
    $normalized = preg_replace('/\s+/u', ' ', trim($normalized));

    // Tokenizimi
    $tokens = preg_split('/\s+/u', trim($normalized), -1, PREG_SPLIT_NO_EMPTY);

    // Lista bazë e fjalëve të ndaluara
    $badWords = [
        'idiot' => 'ofendim',
        'idiote' => 'ofendim',
        'idjote' => 'ofendim',
        'idjot' => 'ofendim',
        'debil' => 'ofendim',
        'debile' => 'ofendim',
        'kafshe' => 'ofendim',
        'budalla' => 'ofendim',
        'budalle' => 'ofendim',
        'budallaqe' => 'ofendim',
        'stupid' => 'ofendim',
        'urrej' => 'gjuhë negative',
        'vrite' => 'kërcënim',
        'vras' => 'kërcënim',
        'rrah' => 'dhunë',
        'trap' => 'ofendim',
        'plehre' => 'ofendim'
    ];

    // Lexon fjalët e ndaluara të personalizuara nga prindi
    if (isset($conn)) {
        $customQ = $conn->query("
            SELECT fjala, lloji 
            FROM fjale_ndaluara_prind
        ");

        if ($customQ) {
            while ($row = $customQ->fetch_assoc()) {
                $fjala = mb_strtolower(trim($row['fjala']), 'UTF-8');
                $lloji = trim($row['lloji']);

                if ($fjala !== '') {
                    $badWords[$fjala] = $lloji !== '' ? $lloji : 'personalizuar';
                }
            }
        }
    }

    // Fjalë që tregojnë se ofendimi i drejtohet një personi
    $personWords = [
        'ti', 'ju', 'ai', 'ajo', 'ata', 'ato',
        'shoku', 'shoqja', 'djali', 'vajza',
        'njeriu', 'personi'
    ];

    // Fjalë që tregojnë objekte jo-personale
    $objectWords = [
        'libri', 'liber', 'filmi', 'film',
        'loja', 'loje', 'kenga', 'kenge',
        'foto', 'video', 'detyrat', 'detyra',
        'ushtrimi', 'ushtrim', 'kompjuteri',
        'telefoni', 'makina', 'aplikacioni',
        'programi', 'sistemi', 'projekti'
    ];

    /*
        Zbulimi i emrave:
        Fjalët me shkronjë të madhe mund të jenë emra personash,
        por objektet si "Filmi", "Libri" nuk duhet të konsiderohen persona.
    */
    $nameWords = [];
    $originalTokens = preg_split('/\s+/u', trim($original), -1, PREG_SPLIT_NO_EMPTY);

    foreach ($originalTokens as $word) {
        $cleanWord = preg_replace('/[^\p{L}\p{N}]/u', '', $word);
        $lowerClean = mb_strtolower($cleanWord, 'UTF-8');

        if ($cleanWord === '') {
            continue;
        }

        // Nëse fjala është objekt, mos e konsidero emër personi
        if (in_array($lowerClean, $objectWords)) {
            continue;
        }

        // Fjalët me shkronjë të madhe, që nuk janë objekte, merren si emra të mundshëm
        if (preg_match('/^[A-ZÇË][\p{L}]+$/u', $cleanWord)) {
            $nameWords[] = $lowerClean;
        }
    }

    $found = [];
    $score = 0;
    $syntacticNotes = [];

    foreach ($tokens as $i => $token) {
        if (isset($badWords[$token])) {
            $found[$token] = $badWords[$token];

            // Kontrollon 3 fjalë para dhe 2 fjalë pas fjalës së ndaluar
            $previousWords = array_slice($tokens, max(0, $i - 3), 3);
            $nextWords = array_slice($tokens, $i + 1, 2);
            $contextWords = array_merge($previousWords, $nextWords);

            $hasPersonContext = false;
            $allPersonWords = array_merge($personWords, $nameWords);

            foreach ($allPersonWords as $person) {
                if (in_array($person, $contextWords)) {
                    $hasPersonContext = true;
                    break;
                }
            }

            $hasObjectContext = false;

            foreach ($objectWords as $object) {
                if (in_array($object, $contextWords)) {
                    $hasObjectContext = true;
                    break;
                }
            }

            /*
                Objekti ka përparësi ndaj personit.
                Kjo shmang rastet si "Filmi ishte idjot",
                ku "Filmi" fillon me shkronjë të madhe por nuk është person.
            */
            if ($hasObjectContext) {
                $score += 5;
                $syntacticNotes[] = 'fjala e ndaluar përdoret për objekt jo-personal';

            } elseif ($hasPersonContext) {
                $score += 35;
                $syntacticNotes[] = 'fjala e ndaluar i drejtohet një personi';

            } else {
                $score += 20;
                $syntacticNotes[] = 'fjala e ndaluar u gjet pa kontekst të qartë personal';
            }
        }
    }

    // Numërimi i shkronjave kapitale
    $uppercaseCount = preg_match_all('/[A-ZÇË]/u', $original);

    if ($uppercaseCount >= 8) {
        $score += 10;
        $syntacticNotes[] = 'përdorim i lartë i shkronjave kapitale';
    }

    // Shenja emocionale të përsëritura
    if (preg_match('/!{2,}|\?{2,}/u', $original)) {
        $score += 10;
        $syntacticNotes[] = 'përdorim i përsëritur i shenjave emocionale';
    }

    // Strukturë e drejtpërdrejtë ndaj personit tjetër
    if (preg_match('/\b(ti|ju)\b.{0,20}\b(je|jeni)\b/u', $lower)) {
        $score += 10;
        $syntacticNotes[] = 'strukturë e drejtpërdrejtë ndaj personit tjetër';
    }

    // Forma urdhërore/agresive
    if (preg_match('/\b(mos|ndalo|ik|largohu)\b/u', $lower)) {
        $score += 8;
        $syntacticNotes[] = 'përdorim i formës urdhërore';
    }

    // Përsëritje fjalësh
    for ($i = 1; $i < count($tokens); $i++) {
        if ($tokens[$i] === $tokens[$i - 1] && mb_strlen($tokens[$i], 'UTF-8') > 2) {
            $score += 7;
            $syntacticNotes[] = 'përsëritje fjale në fjali';
            break;
        }
    }

    $score = min($score, 100);

    if ($score >= 60) {
        $status = 'ofendues';
    } elseif ($score >= 20) {
        $status = 'i_dyshimte';
    } else {
        $status = 'normal';
    }

    return [
        'status' => $status,
        'score' => $score,
        'found_words' => array_keys($found),
        'word_types' => array_values(array_unique($found)),
        'syntactic_notes' => $syntacticNotes,
        'analysis_json' => json_encode([
            'algorithm' => 'LSF - Lexical Features dhe Syntactic Features',
            'lexical_features' => [
                'original_text' => $original,
                'normalized_text' => $normalized,
                'tokens' => $tokens,
                'bad_words' => array_keys($found),
                'bad_word_types' => array_values(array_unique($found)),
                'uppercase_count' => $uppercaseCount
            ],
            'syntactic_features' => $syntacticNotes,
            'score' => $score,
            'status' => $status
        ], JSON_UNESCAPED_UNICODE)
    ];
}
?>