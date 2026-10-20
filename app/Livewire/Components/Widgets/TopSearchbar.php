<?php

namespace App\Livewire\Components\Widgets;

use App\Models\OpacCatalogView;
use App\Models\Student;
use App\Services\EmployeeRoleService;
use Livewire\Component;

class TopSearchbar extends Component
{
    public string $query = '';

    public function updatedQuery(): void
    {
        // State updates trigger component re-render
    }

    public function clearQuery(): void
    {
        $this->query = '';
    }

    public function performSearch()
    {
        $q = trim($this->query);
        if ($q === '') {
            return;
        }

        $isStaff = EmployeeRoleService::isStaff();
        $canViewBooks = $isStaff ? EmployeeRoleService::can(['manage_catalog', 'view_catalog']) : true;
        $canViewStudents = $isStaff ? EmployeeRoleService::can(['manage_users', 'view_students']) : false;
        $routePrefix = EmployeeRoleService::getRoutePrefix();

        if ($isStaff) {
            if ($canViewBooks) {
                return $this->redirect(route($routePrefix . 'book-management.index', ['search' => $q]), navigate: true);
            }
            if ($canViewStudents) {
                return $this->redirect(route($routePrefix . 'user-management'), navigate: true);
            }
        }

        return $this->redirect(route('opac.index', ['search' => $q]), navigate: true);
    }

    public function render()
    {
        $searchTerm = trim($this->query);
        $books = collect();
        $students = collect();
        $isStaff = EmployeeRoleService::isStaff();

        // Permissions check: staff requires catalog/user permissions; students can always search OPAC books
        $canViewBooks = $isStaff ? EmployeeRoleService::can(['manage_catalog', 'view_catalog']) : true;
        $canViewStudents = $isStaff ? EmployeeRoleService::can(['manage_users', 'view_students']) : false;

        if (mb_strlen($searchTerm) >= 2) {
            if ($canViewBooks) {
                try {
                    $books = OpacCatalogView::query()
                        ->search($searchTerm)
                        ->limit(5)
                        ->get();
                } catch (\Throwable $e) {
                    $books = collect();
                }
            }

            if ($canViewStudents) {
                try {
                    $students = Student::with('account')
                        ->where(function ($q) use ($searchTerm) {
                            $q->where('first_name', 'like', "%{$searchTerm}%")
                                ->orWhere('last_name', 'like', "%{$searchTerm}%")
                                ->orWhere('school_id_number', 'like', "%{$searchTerm}%");
                        })
                        ->limit(3)
                        ->get();
                } catch (\Throwable $e) {
                    $students = collect();
                }
            }
        }

        $routePrefix = EmployeeRoleService::getRoutePrefix();

        return view('livewire.components.widgets.top-searchbar', [
            'books' => $books,
            'students' => $students,
            'isStaff' => $isStaff,
            'canViewBooks' => $canViewBooks,
            'canViewStudents' => $canViewStudents,
            'routePrefix' => $routePrefix,
        ]);
    }
}
