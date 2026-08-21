<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\GlobalAnnouncement;

class GlobalAnnouncementsManager extends Component
{
    public $announcements = [];
    public $activeAnnouncement = [
        'id' => null,
        'status' => 'Enabled',
        'display_style' => 'Information',
        'title' => '',
        'message' => '',
        'starts_at' => '',
        'ends_at' => '',
    ];
    public $isEditing = false;
    public $showForm = false;

    public function mount()
    {
        $this->loadAnnouncements();
    }

    public function loadAnnouncements()
    {
        $this->announcements = GlobalAnnouncement::orderBy('created_at', 'desc')->get()->toArray();
    }

    public function create()
    {
        $this->activeAnnouncement = [
            'id' => null,
            'status' => 'Enabled',
            'display_style' => 'Information',
            'title' => '',
            'message' => '',
            'starts_at' => '',
            'ends_at' => '',
        ];
        $this->isEditing = false;
        $this->showForm = true;
    }

    public function edit($id)
    {
        $announcement = GlobalAnnouncement::find($id);
        if ($announcement) {
            $this->activeAnnouncement = $announcement->toArray();
            
            // Format dates for datetime-local input
            if ($this->activeAnnouncement['starts_at']) {
                $this->activeAnnouncement['starts_at'] = date('Y-m-d\TH:i', strtotime($this->activeAnnouncement['starts_at']));
            }
            if ($this->activeAnnouncement['ends_at']) {
                $this->activeAnnouncement['ends_at'] = date('Y-m-d\TH:i', strtotime($this->activeAnnouncement['ends_at']));
            }
            
            $this->isEditing = true;
            $this->showForm = true;
        }
    }

    public function save()
    {
        $this->validate([
            'activeAnnouncement.title' => 'required|string|max:255',
            'activeAnnouncement.message' => 'required|string',
            'activeAnnouncement.status' => 'required|in:Enabled,Disabled',
            'activeAnnouncement.display_style' => 'required|in:Information,Notice,Warning,Critical',
        ]);

        $data = $this->activeAnnouncement;
        $data['starts_at'] = empty($data['starts_at']) ? null : $data['starts_at'];
        $data['ends_at'] = empty($data['ends_at']) ? null : $data['ends_at'];

        if ($this->isEditing && $data['id']) {
            GlobalAnnouncement::where('id', $data['id'])->update([
                'title' => $data['title'],
                'message' => $data['message'],
                'status' => $data['status'],
                'display_style' => $data['display_style'],
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
            ]);
            $this->dispatch('toast', message: 'Announcement updated successfully.');
        } else {
            $data['created_by'] = auth()->id();
            GlobalAnnouncement::create($data);
            $this->dispatch('toast', message: 'Announcement created successfully.');
        }

        $this->showForm = false;
        $this->loadAnnouncements();
    }

    public function cancel()
    {
        $this->showForm = false;
    }

    public function delete($id)
    {
        GlobalAnnouncement::where('id', $id)->delete();
        $this->dispatch('toast', message: 'Announcement deleted.');
        $this->loadAnnouncements();
    }

    public function render()
    {
        return view('livewire.admin.global-announcements-manager');
    }
}
