 <?php
    defined('BASEPATH') or exit('No direct script access allowed');

    class auth extends CI_Controller
    {
        //di berlakukan untuk semua di akses di kontroller authdi simpan di kontroller dan selalu di akses ketika kontroller diakses karena library form falidasion dipakai di banyak
        //punya method defauld untuk selalu dijalankan ketika mengakses kontroler auth
        public function __construct()
        {
            //untuk memanggil method konstruktor di kontroler
            parent::__construct();
            $this->load->library('form_validation');
        }
        public function index()
        {
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
            $this->form_validation->set_rules('password', 'Password', 'trim|required');

            if ($this->form_validation->run() == false) {
                $data['title'] = 'Login Page';
                $this->load->view('template/auth_header', $data);
                $this->load->view('auth/login');
                $this->load->view('template/auth_footer');
            } else {
                //validasinya success
                //agar gampang dibuat method loging, method loging dibuat provate
                $this->_login();
            }
        }

        private function _login()
        {
            $email = $this->input->post('email');
            $password = $this->input->post('password');

            //select * frome tabel user where email = email
            $user = $this->db->get_where('user', ['email' => $email])->row_array();
            // var_dump($user);
            // die;

            // jika user ada
            if ($user) {
                //jika user aktif
                if ($user['is_active'] == 1) {
                    // cek passwordnya 
                    if (password_verify($password, $user['password'])) {
                        $data = [
                            'email' => $user['email'],
                            'role_id' => $user['role_id']
                        ];
                        //disini menyimpen session data user 
                        // disini session tau role id apa yang login
                        $this->session->set_userdata($data);
                        //dia nga langsung masuk tapi cek dulu dia apa
                        if ($user['role_id'] == 1) {
                            redirect('admin');
                        } else {
                            redirect('user');
                        }

                        //arahkan ke controller yang di inginkan
                    } else {
                        //password salah
                        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert" >Wrong password! </div>');
                        redirect('auth');
                    }
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert" >This email has not been activated!  </div>');
                    redirect('auth');
                }
            } //
            //tidak ada user gagal
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert" >email is not registered </div>');
            redirect('auth');
        }

        public function registration()
        {
            //ketika ingin melakukan validasi di form maka memanggil library form falidasion karena form falidation tidak bisa di save di autoload harus di dalam metod atau kontroller

            //buat rule (untuk siapa, nama lain, requaridenya, trimuntuk spasi ga masuk db)

            $this->form_validation->set_rules('name', 'Name', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]', [
                'is_unique' => 'This email has already registered!'
            ]);
            $this->form_validation->set_rules('password1', 'password', 'required|trim|min_length[6]|matches[password2]', [
                'matches' => 'Password dont match !',
                'min_length' => 'Password too short!'
            ]);
            $this->form_validation->set_rules('password2', 'password ', 'required|trim|matches[password1]');


            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'WPU User Registration';
                $this->load->view('template/auth_header', $data);
                $this->load->view('auth/registration');
                $this->load->view('template/auth_footer');
            } else {
                $data = [
                    'name' => htmlspecialchars($this->input->post('name', true)),
                    'email' => htmlspecialchars($this->input->post('email', true)),
                    'image' => 'default.jpg',
                    'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
                    'role_id' => 2,
                    'is_active' => 1,
                    'date_created' => time()
                ];

                $this->db->insert('user', $data);
                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert" >Congratulation ! your account has been created. Please Login </div>');
                redirect('auth');
            }
        }
        public function logout()
        {
            $this->session->unset_userdata('email');
            $this->session->unset_userdata('role_id');

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert" > You have been logged out! </div>');
            redirect('auth');
        }
    }
    //true pada $ data name ini untuk menghindari s=xass /xross sight scripting
    //tambahkan scrip ini:
    //htmlspesialchars($this->input->post('name', true));
    //yang email (untuk mensanitasi input)
    //htmlspesialchars($this->input->post('email', true));

    //untuk login metod nya di kontroler auth
    //row_array() digunakan untuk menggambil satu baris saja
