<?php
class UserController extends Controller {

    private User $model;
    private Driver $driverModel;

    public function __construct() {
        $this->model = new User();
        $this->driverModel = new Driver();
    }

    public function index(): void {
        $this->requireRole('admin');
        $this->view('admin.accounts', [
            'users' => $this->model->all(),
            'flash' => $this->getFlash('success'),
        ]);
    }

    public function create(): void {
        $this->requireRole('admin');
        $this->view('admin.account_form', ['user' => null, 'error' => null]);
    }

    public function store(): void {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $data = [
            'full_name'   => $this->postInput('full_name'),
            'email'       => $this->postInput('email'),
            'username'    => $this->postInput('username'),
            'password'    => $_POST['password'] ?? '',
            'role'        => $_POST['role']     ?? 'requester',
            'department'  => $this->postInput('department'),
            'contact_no'  => $this->postInput('contact_no'),
        ];

        foreach (['full_name','email','username','password'] as $f) {
            if (empty($data[$f])) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'All required fields must be filled.'], 422);
                }
                $this->flash('error', 'All required fields must be filled.');
                $this->redirect('admin/accounts/create');
            }
        }


        $validRoles = ['admin','staff','requester','driver'];
        if (!in_array($data['role'], $validRoles, true)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Invalid role selected.'], 422);
            }
            $this->flash('error', 'Invalid role selected.');
            $this->redirect('admin/accounts/create');
        }

        $userId = null;

        try {
            $userId = $this->model->create($data);
            if ($data['role'] === 'driver') {
                $this->driverModel->createForUser($userId, true);
            }
        } catch (\PDOException $e) {
            if ($userId && $data['role'] === 'driver') {
                $this->model->delete($userId);
            }
            $msg = 'Failed to create account.';
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $msg = 'Email or username already exists.';
            }
            if ($data['role'] === 'driver') {
                $msg = 'Failed to create driver profile.';
            }
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $msg], 422);
            }
            $this->flash('error', $msg);
            $this->redirect('admin/accounts/create');
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Account created successfully.', 'redirect' => BASE_URL . 'admin/accounts']);
        }
        $this->flash('success', 'Account created successfully.');
        $this->redirect('admin/accounts');
    }

    public function edit(): void {
        $this->requireRole('admin');
        $id   = (int)($_GET['id'] ?? 0);
        $user = $this->model->findById($id);
        if (!$user) { http_response_code(404); die('User not found.'); }
        $this->view('admin.account_form', ['user' => $user, 'error' => null]);
    }

    public function update(): void {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $id   = (int)($_POST['user_id'] ?? 0);
        $data = [
            'full_name'  => $this->postInput('full_name'),
            'email'      => $this->postInput('email'),
            'role'       => $_POST['role']     ?? 'requester',
            'department' => $this->postInput('department'),
            'contact_no' => $this->postInput('contact_no'),
            'password'   => $_POST['password'] ?? '',
        ];

        try {
            $this->model->update($id, $data);

            if ($data['role'] === 'driver') {
                $driver = $this->driverModel->findByUserId($id);
                $available = $driver ? (bool)$driver['is_available'] : true;
                if ($driver) {
                    $this->driverModel->updateByUserId($id, $available);
                } else {
                    $this->driverModel->createForUser($id, $available);
                }
            }
        } catch (\PDOException $e) {
            $msg = 'Failed to update account.';
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $msg = 'Email already exists.';
            }
            if ($data['role'] === 'driver') {
                $msg = 'Failed to update driver profile.';
            }
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $msg], 422);
            }
            $this->flash('error', $msg);
            $this->redirect('admin/accounts');
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Account updated successfully.', 'redirect' => BASE_URL . 'admin/accounts']);
        }
        $this->flash('success', 'Account updated successfully.');
        $this->redirect('admin/accounts');
    }

    public function editProfile(): void {
        $this->requireRole('requester', 'staff', 'driver');

        $id = (int)($_SESSION['user_id'] ?? 0);
        $user = $this->model->findById($id);
        if (!$user) { http_response_code(404); die('User not found.'); }

        $this->view('account.edit', [
            'user'  => $user,
            'flash' => $this->getFlash('success'),
            'error' => $this->getFlash('error'),
        ]);
    }

    public function updateProfile(): void {
        $this->requireRole('requester', 'staff', 'driver');
        $this->verifyCsrf();

        $id = (int)($_SESSION['user_id'] ?? 0);
        $data = [
            'full_name'  => $this->postInput('full_name'),
            'email'      => $this->postInput('email'),
            'department' => $this->postInput('department'),
            'contact_no' => $this->postInput('contact_no'),
            'password'   => $_POST['password'] ?? '',
        ];

        foreach (['full_name','email'] as $f) {
            if (empty($data[$f])) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Name and email are required.'], 422);
                }
                $this->flash('error', 'Name and email are required.');
                $this->redirect('account/edit');
            }
        }

        try {
            $this->model->updateProfile($id, $data);
        } catch (\PDOException $e) {
            $msg = 'Failed to update profile.';
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $msg = 'Email already exists.';
            }
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $msg], 422);
            }
            $this->flash('error', $msg);
            $this->redirect('account/edit');
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Profile updated successfully.']);
        }
        $this->flash('success', 'Profile updated successfully.');
        $this->redirect('account/edit');
    }

    public function toggle(): void {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $id = (int)($_POST['user_id'] ?? 0);
        $this->model->toggleActive($id);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Account status updated.']);
        }
        $this->flash('success', 'Account status updated.');
        $this->redirect('admin/accounts');
    }
}