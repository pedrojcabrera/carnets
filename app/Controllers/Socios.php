<?php

namespace App\Controllers;

use App\Models\SocioModel;

/**
 * Controlador CRUD completo de socios.
 * Incluye subida de foto del socio.
 */
class Socios extends BaseController
{
    protected SocioModel $model;

    public function __construct()
    {
        $this->model = new SocioModel();
    }

    /**
     * Listado paginado de socios.
     */
    public function index(): string
    {
        $socios = $this->model->paginate(15);

        return view('layouts/main', [
            'titulo'      => 'Gestión de Socios',
            'view_content' => 'socios/index',
            'socios'      => $socios,
            'pager'       => $this->model->pager,
        ]);
    }

    /**
     * Formulario para crear un nuevo socio.
     */
    public function crear(): string
    {
        return view('layouts/main', [
            'titulo'      => 'Nuevo Socio',
            'view_content' => 'socios/create',
        ]);
    }

    /**
     * Guarda el nuevo socio en base de datos.
     */
    public function guardar()
    {
        $rules = $this->model->validationRules + [
            'foto' => 'if_exist|uploaded[foto]|max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors())
                ->withInput();
        }

        $urlFoto = null;
        $file    = $this->request->getFile('foto');

        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $nombreFoto = $file->getRandomName();
            $file->move(FCPATH . 'uploads/socios', $nombreFoto);
            $urlFoto = '/uploads/socios/' . $nombreFoto;
        }

        $this->model->insert([
            'nombre_completo' => $this->request->getPost('nombre_completo'),
            'num_socio'       => $this->request->getPost('num_socio'),
            'dni'             => strtoupper(trim((string) $this->request->getPost('dni'))),
            'email'           => trim((string) $this->request->getPost('email')),
            'tipo_socio'      => $this->request->getPost('tipo_socio'),
            'valido_hasta'    => $this->request->getPost('valido_hasta'),
            'url_foto'        => $urlFoto,
        ]);

        return redirect()->to('/socios')->with('success', 'Socio creado correctamente.');
    }

    /**
     * Formulario para editar un socio existente.
     */
    public function editar(int $id): string
    {
        $socio = $this->model->find($id);

        if (! is_object($socio)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Socio no encontrado.');
        }

        return view('layouts/main', [
            'titulo'       => 'Editar Socio',
            'view_content' => 'socios/edit',
            'socio'        => $socio,
        ]);
    }

    /**
     * Actualiza los datos de un socio.
     */
    public function actualizar(int $id)
    {
        $socio = $this->model->find($id);

        if (! is_object($socio)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Socio no encontrado.');
        }

        // Las reglas de unicidad excluyen el propio registro
        $rules = [
            'nombre_completo' => 'required|min_length[3]|max_length[255]',
            'num_socio'       => 'required|max_length[50]',
            'dni'             => 'required|min_length[7]|max_length[20]|is_unique[socios.dni,id,' . $id . ']',
            'email'           => 'required|valid_email|max_length[255]|is_unique[socios.email,id,' . $id . ']',
            'tipo_socio'      => 'required|in_list[Socio/a,Colaborador/a]',
            'valido_hasta'    => 'required|valid_date',
            'foto'            => 'if_exist|uploaded[foto]|max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors())
                ->withInput();
        }

        $datos = [
            'nombre_completo' => $this->request->getPost('nombre_completo'),
            'num_socio'       => $this->request->getPost('num_socio'),
            'dni'             => strtoupper(trim((string) $this->request->getPost('dni'))),
            'email'           => trim((string) $this->request->getPost('email')),
            'tipo_socio'      => $this->request->getPost('tipo_socio'),
            'valido_hasta'    => $this->request->getPost('valido_hasta'),
        ];

        // Subir nueva foto si se ha enviado
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $nombreFoto      = $file->getRandomName();
            $file->move(FCPATH . 'uploads/socios', $nombreFoto);
            $datos['url_foto'] = '/uploads/socios/' . $nombreFoto;
        }

        $this->model->update($id, $datos);

        return redirect()->to('/socios')->with('success', 'Socio actualizado correctamente.');
    }

    /**
     * Elimina un socio de la base de datos.
     */
    public function eliminar(int $id)
    {
        $socio = $this->model->find($id);

        if (! is_object($socio)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Socio no encontrado.');
        }

        // Eliminar foto física si existe
        if (! empty($socio->url_foto)) {
            $rutaFoto = FCPATH . ltrim($socio->url_foto, '/');
            if (file_exists($rutaFoto)) {
                unlink($rutaFoto);
            }
        }

        $this->model->delete($id);

        return redirect()->to('/socios')->with('success', 'Socio eliminado correctamente.');
    }
}
