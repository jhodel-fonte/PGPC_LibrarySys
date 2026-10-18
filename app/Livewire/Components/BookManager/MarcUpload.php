<?php

namespace App\Livewire\Components\BookManager;

use Livewire\Component;
use Livewire\Attributes\On;

class MarcUpload extends Component
{
    public string $activeTab = 'all'; // 'all', 'ready', 'warnings'
    public string $search = '';
    public array $selectedRecords = ['1', '2', '3', '4', '5']; // Default select ready records
    public bool $selectAll = false;

    // Configuration passed from parent or defaults
    public string $defaultCollection = 'General Circulation';
    public int $defaultCopies = 1;
    public string $accessionPrefix = 'PGPC-2026-';
    public int $startAccessionNumber = 1042;

    // File Summary Info
    public array $fileSummary = [
        'name' => 'PGPC_Acquisitions_2026_Batch_A.mrc',
        'size' => '184.2 KB',
        'format' => 'MARC 21 Bibliographic (ISO 2709 Binary)',
        'encoding' => 'UTF-8 Unicode',
        'uploaded_at' => 'October 19, 2026 at 10:45 AM',
        'total_parsed' => 7,
        'ready_count' => 5,
        'warnings_count' => 2,
    ];

    // Raw MARC Tag Modal
    public ?array $activeRecordModal = null;
    public bool $showMarcModal = false;

    // Import action simulation
    public bool $importCompleted = false;
    public int $importedCount = 0;

    /**
     * Mock Extracted Bibliographic Records from MARC File
     */
    public array $records = [
        [
            'id' => '1',
            'control_number' => 'PGPC-MRC-001042',
            'leader' => '01452nam a2200385 i 4500',
            'title' => 'Introduction to Algorithms',
            'subtitle' => 'Fourth Edition',
            'authors' => 'Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, Clifford Stein',
            'primary_author' => 'Cormen, Thomas H.',
            'isbn' => '978-0-262-04630-5',
            'call_number' => '005.1 COR 2022',
            'dewey' => '005.1',
            'publisher' => 'The MIT Press',
            'place' => 'Cambridge, Massachusetts',
            'year' => '2022',
            'edition' => 'Fourth edition',
            'pages' => 'xix, 1292 pages',
            'subjects' => ['Computer algorithms', 'Data structures (Computer science)', 'Computational complexity'],
            'language' => 'English',
            'status' => 'ready',
            'warning_message' => null,
            'marc_fields' => [
                ['tag' => 'LDR', 'ind1' => '', 'ind2' => '', 'data' => '01452nam a2200385 i 4500', 'desc' => 'Record Leader'],
                ['tag' => '001', 'ind1' => '', 'ind2' => '', 'data' => 'ocn946571234', 'desc' => 'Control Number'],
                ['tag' => '008', 'ind1' => '', 'ind2' => '', 'data' => '220115s2022    maub     b    001 0 eng  ', 'desc' => 'Fixed-Length Data Elements'],
                ['tag' => '020', 'ind1' => '#', 'ind2' => '#', 'data' => '$a 9780262046305 $q hardcover', 'desc' => 'International Standard Book Number (ISBN)'],
                ['tag' => '082', 'ind1' => '0', 'ind2' => '4', 'data' => '$a 005.1 $2 23', 'desc' => 'Dewey Decimal Classification'],
                ['tag' => '100', 'ind1' => '1', 'ind2' => '#', 'data' => '$a Cormen, Thomas H., $e author.', 'desc' => 'Main Entry -- Personal Name'],
                ['tag' => '245', 'ind1' => '1', 'ind2' => '0', 'data' => '$a Introduction to algorithms / $c Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, Clifford Stein.', 'desc' => 'Title Statement'],
                ['tag' => '250', 'ind1' => '#', 'ind2' => '#', 'data' => '$a Fourth edition.', 'desc' => 'Edition Statement'],
                ['tag' => '264', 'ind1' => '#', 'ind2' => '1', 'data' => '$a Cambridge, Massachusetts : $b The MIT Press, $c [2022]', 'desc' => 'Production, Publication, Distribution (Publisher)'],
                ['tag' => '300', 'ind1' => '#', 'ind2' => '#', 'data' => '$a xix, 1292 pages : $b illustrations ; $c 24 cm', 'desc' => 'Physical Description'],
                ['tag' => '650', 'ind1' => '#', 'ind2' => '0', 'data' => '$a Computer algorithms.', 'desc' => 'Subject Added Entry -- Topical Term'],
                ['tag' => '650', 'ind1' => '#', 'ind2' => '0', 'data' => '$a Data structures (Computer science)', 'desc' => 'Subject Added Entry -- Topical Term'],
            ],
        ],
        [
            'id' => '2',
            'control_number' => 'PGPC-MRC-001043',
            'leader' => '01210nam a2200325 a 4500',
            'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
            'subtitle' => 'A Handbook of Agile Software Craftsmanship',
            'authors' => 'Robert C. Martin',
            'primary_author' => 'Martin, Robert C.',
            'isbn' => '978-0-13-235088-4',
            'call_number' => '005.1 MAR 2008',
            'dewey' => '005.1',
            'publisher' => 'Prentice Hall',
            'place' => 'Upper Saddle River, NJ',
            'year' => '2008',
            'edition' => '1st edition',
            'pages' => 'xxix, 431 pages',
            'subjects' => ['Agile software development', 'Software refactoring', 'Computer programming'],
            'language' => 'English',
            'status' => 'ready',
            'warning_message' => null,
            'marc_fields' => [
                ['tag' => 'LDR', 'ind1' => '', 'ind2' => '', 'data' => '01210nam a2200325 a 4500', 'desc' => 'Record Leader'],
                ['tag' => '001', 'ind1' => '', 'ind2' => '', 'data' => 'ocn213849182', 'desc' => 'Control Number'],
                ['tag' => '008', 'ind1' => '', 'ind2' => '', 'data' => '080710s2008    nju      b    001 0 eng  ', 'desc' => 'Fixed-Length Data Elements'],
                ['tag' => '020', 'ind1' => '#', 'ind2' => '#', 'data' => '$a 9780132350884', 'desc' => 'International Standard Book Number (ISBN)'],
                ['tag' => '082', 'ind1' => '0', 'ind2' => '4', 'data' => '$a 005.1 $2 22', 'desc' => 'Dewey Decimal Classification'],
                ['tag' => '100', 'ind1' => '1', 'ind2' => '#', 'data' => '$a Martin, Robert C., $e author.', 'desc' => 'Main Entry -- Personal Name'],
                ['tag' => '245', 'ind1' => '1', 'ind2' => '0', 'data' => '$a Clean code : $b a handbook of agile software craftsmanship / $c Robert C. Martin.', 'desc' => 'Title Statement'],
                ['tag' => '264', 'ind1' => '#', 'ind2' => '1', 'data' => '$a Upper Saddle River, NJ : $b Prentice Hall, $c [2008]', 'desc' => 'Production, Publication, Distribution (Publisher)'],
                ['tag' => '300', 'ind1' => '#', 'ind2' => '#', 'data' => '$a xxix, 431 pages : $b illustrations ; $c 24 cm', 'desc' => 'Physical Description'],
                ['tag' => '650', 'ind1' => '#', 'ind2' => '0', 'data' => '$a Agile software development.', 'desc' => 'Subject Added Entry -- Topical Term'],
            ],
        ],
        [
            'id' => '3',
            'control_number' => 'PGPC-MRC-001044',
            'leader' => '01530nam a2200410 i 4500',
            'title' => 'Database System Concepts',
            'subtitle' => 'Seventh Edition',
            'authors' => 'Abraham Silberschatz, Henry F. Korth, S. Sudarshan',
            'primary_author' => 'Silberschatz, Abraham',
            'isbn' => '978-0-07-802215-9',
            'call_number' => '005.74 SIL 2019',
            'dewey' => '005.74',
            'publisher' => 'McGraw-Hill Education',
            'place' => 'New York, NY',
            'year' => '2019',
            'edition' => '7th edition',
            'pages' => 'xxvi, 1349 pages',
            'subjects' => ['Database management', 'Relational databases', 'SQL'],
            'language' => 'English',
            'status' => 'ready',
            'warning_message' => null,
            'marc_fields' => [
                ['tag' => 'LDR', 'ind1' => '', 'ind2' => '', 'data' => '01530nam a2200410 i 4500', 'desc' => 'Record Leader'],
                ['tag' => '001', 'ind1' => '', 'ind2' => '', 'data' => 'ocn1089842103', 'desc' => 'Control Number'],
                ['tag' => '020', 'ind1' => '#', 'ind2' => '#', 'data' => '$a 9780078022159', 'desc' => 'International Standard Book Number (ISBN)'],
                ['tag' => '082', 'ind1' => '0', 'ind2' => '4', 'data' => '$a 005.74 $2 23', 'desc' => 'Dewey Decimal Classification'],
                ['tag' => '100', 'ind1' => '1', 'ind2' => '#', 'data' => '$a Silberschatz, Abraham, $e author.', 'desc' => 'Main Entry -- Personal Name'],
                ['tag' => '245', 'ind1' => '1', 'ind2' => '0', 'data' => '$a Database system concepts / $c Abraham Silberschatz, Henry F. Korth, S. Sudarshan.', 'desc' => 'Title Statement'],
                ['tag' => '250', 'ind1' => '#', 'ind2' => '#', 'data' => '$a Seventh edition.', 'desc' => 'Edition Statement'],
                ['tag' => '264', 'ind1' => '#', 'ind2' => '1', 'data' => '$a New York, NY : $b McGraw-Hill, $c [2019]', 'desc' => 'Production, Publication, Distribution (Publisher)'],
                ['tag' => '650', 'ind1' => '#', 'ind2' => '0', 'data' => '$a Database management.', 'desc' => 'Subject Added Entry -- Topical Term'],
            ],
        ],
        [
            'id' => '4',
            'control_number' => 'PGPC-MRC-001045',
            'leader' => '01620nam a2200445 i 4500',
            'title' => 'Artificial Intelligence: A Modern Approach',
            'subtitle' => 'Global Edition, 4th Edition',
            'authors' => 'Stuart Russell, Peter Norvig',
            'primary_author' => 'Russell, Stuart J.',
            'isbn' => '978-1-292-40113-3',
            'call_number' => '006.3 RUS 2021',
            'dewey' => '006.3',
            'publisher' => 'Pearson Education',
            'place' => 'Harlow, England',
            'year' => '2021',
            'edition' => 'Fourth edition, Global edition',
            'pages' => '1166 pages',
            'subjects' => ['Artificial intelligence', 'Machine learning', 'Knowledge representation'],
            'language' => 'English',
            'status' => 'ready',
            'warning_message' => null,
            'marc_fields' => [
                ['tag' => 'LDR', 'ind1' => '', 'ind2' => '', 'data' => '01620nam a2200445 i 4500', 'desc' => 'Record Leader'],
                ['tag' => '001', 'ind1' => '', 'ind2' => '', 'data' => 'ocn1230194821', 'desc' => 'Control Number'],
                ['tag' => '020', 'ind1' => '#', 'ind2' => '#', 'data' => '$a 9781292401133', 'desc' => 'International Standard Book Number (ISBN)'],
                ['tag' => '082', 'ind1' => '0', 'ind2' => '4', 'data' => '$a 006.3 $2 23', 'desc' => 'Dewey Decimal Classification'],
                ['tag' => '100', 'ind1' => '1', 'ind2' => '#', 'data' => '$a Russell, Stuart J., $e author.', 'desc' => 'Main Entry -- Personal Name'],
                ['tag' => '245', 'ind1' => '1', 'ind2' => '0', 'data' => '$a Artificial intelligence : $b a modern approach / $c Stuart Russell, Peter Norvig.', 'desc' => 'Title Statement'],
                ['tag' => '264', 'ind1' => '#', 'ind2' => '1', 'data' => '$a Harlow, England : $b Pearson, $c [2021]', 'desc' => 'Production, Publication, Distribution (Publisher)'],
                ['tag' => '650', 'ind1' => '#', 'ind2' => '0', 'data' => '$a Artificial intelligence.', 'desc' => 'Subject Added Entry -- Topical Term'],
            ],
        ],
        [
            'id' => '5',
            'control_number' => 'PGPC-MRC-001046',
            'leader' => '01190nam a2200310 i 4500',
            'title' => 'Panitikang Pilipino: Kasaysayan, Teorya, at Praktika',
            'subtitle' => 'Muling Pagbasa sa mga Obra Maestra',
            'authors' => 'Bienvenido Lumbera, Cynthia Nograles Lumbera',
            'primary_author' => 'Lumbera, Bienvenido',
            'isbn' => '978-971-542-612-1',
            'call_number' => 'FIL 899.211 LUM 2020',
            'dewey' => '899.211',
            'publisher' => 'University of the Philippines Press',
            'place' => 'Quezon City, Philippines',
            'year' => '2020',
            'edition' => 'Bagong edisyon',
            'pages' => '348 pages',
            'subjects' => ['Philippine literature', 'Tagalog literature -- History and criticism', 'Filipiniana Collection'],
            'language' => 'Filipino',
            'status' => 'ready',
            'warning_message' => null,
            'marc_fields' => [
                ['tag' => 'LDR', 'ind1' => '', 'ind2' => '', 'data' => '01190nam a2200310 i 4500', 'desc' => 'Record Leader'],
                ['tag' => '001', 'ind1' => '', 'ind2' => '', 'data' => 'phl202008492', 'desc' => 'Control Number'],
                ['tag' => '020', 'ind1' => '#', 'ind2' => '#', 'data' => '$a 9789715426121', 'desc' => 'International Standard Book Number (ISBN)'],
                ['tag' => '082', 'ind1' => '0', 'ind2' => '4', 'data' => '$a 899.211 $2 23', 'desc' => 'Dewey Decimal Classification'],
                ['tag' => '100', 'ind1' => '1', 'ind2' => '#', 'data' => '$a Lumbera, Bienvenido, $e author.', 'desc' => 'Main Entry -- Personal Name'],
                ['tag' => '245', 'ind1' => '1', 'ind2' => '0', 'data' => '$a Panitikang Pilipino : $b kasaysayan, teorya, at praktika / $c Bienvenido Lumbera, Cynthia Nograles Lumbera.', 'desc' => 'Title Statement'],
                ['tag' => '264', 'ind1' => '#', 'ind2' => '1', 'data' => '$a Lungsod Quezon : $b University of the Philippines Press, $c [2020]', 'desc' => 'Production, Publication, Distribution (Publisher)'],
                ['tag' => '650', 'ind1' => '#', 'ind2' => '4', 'data' => '$a Philippine literature $x History and criticism.', 'desc' => 'Subject Added Entry -- Topical Term'],
            ],
        ],
        [
            'id' => '6',
            'control_number' => 'PGPC-MRC-001047',
            'leader' => '01050nam a2200280 u 4500',
            'title' => 'Principles of Data Science and Analytics',
            'subtitle' => 'Practical Applications for Academic Research',
            'authors' => 'Sinan Ozdemir',
            'primary_author' => 'Ozdemir, Sinan',
            'isbn' => '978-1-78980-454-6',
            'call_number' => 'UNASSIGNED',
            'dewey' => '',
            'publisher' => 'Packt Publishing',
            'place' => 'Birmingham, UK',
            'year' => '2023',
            'edition' => '2nd edition',
            'pages' => '412 pages',
            'subjects' => ['Data science', 'Python programming', 'Mathematical statistics'],
            'language' => 'English',
            'status' => 'warning',
            'warning_message' => 'Missing Call Number & Dewey Classification (MARC Field 082/050 is empty).',
            'marc_fields' => [
                ['tag' => 'LDR', 'ind1' => '', 'ind2' => '', 'data' => '01050nam a2200280 u 4500', 'desc' => 'Record Leader'],
                ['tag' => '001', 'ind1' => '', 'ind2' => '', 'data' => 'ocn1349817290', 'desc' => 'Control Number'],
                ['tag' => '020', 'ind1' => '#', 'ind2' => '#', 'data' => '$a 9781789804546', 'desc' => 'International Standard Book Number (ISBN)'],
                ['tag' => '082', 'ind1' => '#', 'ind2' => '#', 'data' => '[EMPTY]', 'desc' => 'Dewey Decimal Classification (Missing)'],
                ['tag' => '100', 'ind1' => '1', 'ind2' => '#', 'data' => '$a Ozdemir, Sinan, $e author.', 'desc' => 'Main Entry -- Personal Name'],
                ['tag' => '245', 'ind1' => '1', 'ind2' => '0', 'data' => '$a Principles of data science and analytics / $c Sinan Ozdemir.', 'desc' => 'Title Statement'],
                ['tag' => '264', 'ind1' => '#', 'ind2' => '1', 'data' => '$a Birmingham, UK : $b Packt Publishing, $c 2023.', 'desc' => 'Production, Publication, Distribution (Publisher)'],
                ['tag' => '650', 'ind1' => '#', 'ind2' => '0', 'data' => '$a Data science.', 'desc' => 'Subject Added Entry -- Topical Term'],
            ],
        ],
        [
            'id' => '7',
            'control_number' => 'PGPC-MRC-001048',
            'leader' => '00980nam a2200260 u 4500',
            'title' => 'Padre Garcia Historical Annals & Heritage Records',
            'subtitle' => 'Archival Documentation Survey (1949-2024)',
            'authors' => 'Batangas Provincial Historical Commission',
            'primary_author' => 'Batangas Provincial Historical Commission',
            'isbn' => '978-621-8012-99-0',
            'call_number' => 'FIL 959.91 BPH 2024',
            'dewey' => '959.91',
            'publisher' => '[s.n.] Institutional Archival Press',
            'place' => 'Batangas City',
            'year' => '2024',
            'edition' => 'Commemorative Monograph',
            'pages' => '198 pages',
            'subjects' => ['Padre Garcia (Batangas, Philippines) -- History', 'Batangas (Philippines) -- Heritage'],
            'language' => 'English & Filipino',
            'status' => 'warning',
            'warning_message' => 'Publisher entity needs authority verification (MARC Field 264$b lacks authority code).',
            'marc_fields' => [
                ['tag' => 'LDR', 'ind1' => '', 'ind2' => '', 'data' => '00980nam a2200260 u 4500', 'desc' => 'Record Leader'],
                ['tag' => '001', 'ind1' => '', 'ind2' => '', 'data' => 'phl202499104', 'desc' => 'Control Number'],
                ['tag' => '020', 'ind1' => '#', 'ind2' => '#', 'data' => '$a 9786218012990', 'desc' => 'International Standard Book Number (ISBN)'],
                ['tag' => '082', 'ind1' => '0', 'ind2' => '4', 'data' => '$a 959.91 $2 23', 'desc' => 'Dewey Decimal Classification'],
                ['tag' => '110', 'ind1' => '2', 'ind2' => '#', 'data' => '$a Batangas Provincial Historical Commission.', 'desc' => 'Main Entry -- Corporate Name'],
                ['tag' => '245', 'ind1' => '1', 'ind2' => '0', 'data' => '$a Padre Garcia historical annals & heritage records / $c Batangas Provincial Historical Commission.', 'desc' => 'Title Statement'],
                ['tag' => '264', 'ind1' => '#', 'ind2' => '1', 'data' => '$a Batangas City : $b [s.n.] Institutional Archival Press, $c 2024.', 'desc' => 'Production, Publication, Distribution (Publisher)'],
                ['tag' => '651', 'ind1' => '#', 'ind2' => '0', 'data' => '$a Padre Garcia (Batangas, Philippines) $x History.', 'desc' => 'Subject Added Entry -- Geographic Name'],
            ],
        ],
    ];

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function toggleSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selectedRecords = [];
            $this->selectAll = false;
        } else {
            $this->selectedRecords = array_map(fn($r) => (string)$r['id'], $this->filteredRecords);
            $this->selectAll = true;
        }
    }

    public function selectOnlyReady(): void
    {
        $ready = array_filter($this->records, fn($r) => $r['status'] === 'ready');
        $this->selectedRecords = array_map(fn($r) => (string)$r['id'], $ready);
        $this->selectAll = count($this->selectedRecords) === count($this->filteredRecords);
    }

    public function openMarcModal(string $recordId): void
    {
        foreach ($this->records as $r) {
            if ((string)$r['id'] === (string)$recordId) {
                $this->activeRecordModal = $r;
                $this->showMarcModal = true;
                return;
            }
        }
    }

    public function closeMarcModal(): void
    {
        $this->showMarcModal = false;
        $this->activeRecordModal = null;
    }

    public function removeRecord(string $recordId): void
    {
        $this->records = array_values(array_filter($this->records, fn($r) => (string)$r['id'] !== (string)$recordId));
        $this->selectedRecords = array_values(array_filter($this->selectedRecords, fn($id) => (string)$id !== (string)$recordId));
        $this->recalculateCounts();
    }

    public function recalculateCounts(): void
    {
        $this->fileSummary['total_parsed'] = count($this->records);
        $this->fileSummary['ready_count'] = count(array_filter($this->records, fn($r) => $r['status'] === 'ready'));
        $this->fileSummary['warnings_count'] = count(array_filter($this->records, fn($r) => $r['status'] === 'warning'));
    }

    public function simulateImport(): void
    {
        $count = count($this->selectedRecords);
        if ($count === 0) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'title' => 'No Records Selected',
                'message' => 'Please select at least one record to import into the library collection.'
            ]);
            return;
        }

        // Open the reusable centered loading modal with backdrop page blur
        $this->dispatch('open-modal',
            title: 'Importing MARC Records',
            message: "Extracting bibliographic data and cataloging {$count} selected books into {$this->defaultCollection}...",
            type: 'loading',
            options: [
                'autoCloseMs' => 2000,
                'completeEvent' => 'marc-import-completed',
            ]
        );
    }

    #[On('marc-import-completed')]
    public function onImportCompleted(): void
    {
        $count = count($this->selectedRecords);
        $this->importedCount = $count;
        $this->importCompleted = true;

        $this->dispatch('show-toast', [
            'type' => 'success',
            'title' => 'Import Successful',
            'message' => "Successfully extracted and cataloged {$count} records into {$this->defaultCollection}."
        ]);
    }

    public function resetSimulation(): void
    {
        $this->importCompleted = false;
        $this->importedCount = 0;
    }

    public function getFilteredRecordsProperty(): array
    {
        return array_filter($this->records, function ($r) {
            // Tab filter
            if ($this->activeTab === 'ready' && $r['status'] !== 'ready') {
                return false;
            }
            if ($this->activeTab === 'warnings' && $r['status'] !== 'warning') {
                return false;
            }

            // Search filter
            if (!empty($this->search)) {
                $term = strtolower(trim($this->search));
                $inTitle = str_contains(strtolower($r['title']), $term);
                $inAuthor = str_contains(strtolower($r['authors']), $term);
                $inIsbn = str_contains(strtolower($r['isbn']), $term);
                $inCall = str_contains(strtolower($r['call_number']), $term);
                $inPublisher = str_contains(strtolower($r['publisher']), $term);

                return $inTitle || $inAuthor || $inIsbn || $inCall || $inPublisher;
            }

            return true;
        });
    }

    public function render()
    {
        $routePrefix = request()->is('librarian*') ? 'librarian.' : 'admin.';

        return view('livewire.components.book-manager.marc-upload', [
            'routePrefix' => $routePrefix,
            'filteredRecords' => $this->filteredRecords,
        ]);
    }
}
