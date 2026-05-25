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
     * Listado completo de socios con busqueda y ordenacion.
     */
    public function index()
    {
        $q = trim((string) $this->request->getGet('q'));
        $sort = (string) $this->request->getGet('sort');
        $dir = strtolower((string) $this->request->getGet('dir')) === 'desc' ? 'desc' : 'asc';

        $sortMap = [
            'id'             => 'id',
            'nombre_completo' => 'nombre_completo',
            'tipo_socio'     => 'tipo_socio',
            'dni'            => 'dni',
            'email'          => 'email',
            'num_socio'      => 'num_socio',
            'valido_hasta'   => 'valido_hasta',
            'created_at'     => 'created_at',
        ];

        if (! array_key_exists($sort, $sortMap)) {
            $sort = 'nombre_completo';
        }

        $builder = $this->model->builder();

        if ($q !== '') {
            $builder->groupStart()
                ->like('nombre_completo', $q)
                ->orLike('tipo_socio', $q)
                ->orLike('dni', $q)
                ->orLike('email', $q)
                ->orLike('num_socio', $q)
                ->orLike('valido_hasta', $q)
                ->orLike('created_at', $q)
                ->groupEnd();
        }

        $socios = $builder
            ->orderBy($sortMap[$sort], $dir)
            ->get()
            ->getResultObject();

        foreach ($socios as $socio) {
            $faltantes = $this->getMissingCarnetFields($socio);
            $socio->carnet_disponible = $faltantes === [];
            $socio->carnet_faltantes = $faltantes;
        }

        return view('layouts/main', [
            'titulo'      => 'Gestión de Socios',
            'view_content' => 'socios/index',
            'socios'       => $socios,
            'q'            => $q,
            'sort'         => $sort,
            'dir'          => $dir,
        ]);
    }

    /**
     * Devuelve los datos que faltan para generar el carnet de un socio.
     *
     * @return list<string>
     */
    private function getMissingCarnetFields(object $socio): array
    {
        $required = [
            'dni',
            'nombre_completo',
            'num_socio',
            'tipo_socio',
            'valido_hasta',
            'url_foto',
        ];

        $labels = [
            'dni' => 'DNI',
            'nombre_completo' => 'nombre completo',
            'num_socio' => 'numero de socio',
            'tipo_socio' => 'tipo de socio',
            'valido_hasta' => 'fecha de validez',
            'url_foto' => 'foto',
        ];

        $missing = [];

        foreach ($required as $field) {
            $value = $socio->{$field} ?? null;

            if ($value === null || trim((string) $value) === '') {
                $missing[] = $labels[$field] ?? $field;
                continue;
            }

            if ($field === 'url_foto') {
                $rutaFoto = FCPATH . ltrim((string) $value, '/');
                if (! is_file($rutaFoto)) {
                    $missing[] = $labels[$field] ?? $field;
                }
            }
        }

        return array_values(array_unique($missing));
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

        return redirect()->to('/index.php/socios')->with('success', 'Socio creado correctamente.');
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

        $eliminarFoto = $this->request->getPost('eliminar_foto') === '1';
        $file = $this->request->getFile('foto');
        $hayNuevaFoto = $file && $file->isValid() && ! $file->hasMoved();

        // Las reglas de unicidad excluyen el propio registro
        $rules = [
            'nombre_completo' => 'required|min_length[3]|max_length[255]',
            'num_socio'       => 'required|max_length[50]',
            'dni'             => 'required|min_length[7]|max_length[20]|is_unique[socios.dni,id,' . $id . ']',
            'email'           => 'required|valid_email|max_length[255]|is_unique[socios.email,id,' . $id . ']',
            'tipo_socio'      => 'required|in_list[Socio/a,Colaborador/a]',
            'valido_hasta'    => 'required|valid_date',
        ];

        if ($hayNuevaFoto) {
            $rules['foto'] = 'max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]';
        }

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

        if ($eliminarFoto && ! empty($socio->url_foto)) {
            $rutaFotoActual = FCPATH . ltrim((string) $socio->url_foto, '/');

            if (is_file($rutaFotoActual)) {
                unlink($rutaFotoActual);
            }

            $datos['url_foto'] = null;
        }

        // Subir nueva foto si se ha enviado; reemplaza la anterior si existe.
        if ($hayNuevaFoto) {
            if (! empty($socio->url_foto)) {
                $rutaFotoActual = FCPATH . ltrim((string) $socio->url_foto, '/');

                if (is_file($rutaFotoActual)) {
                    unlink($rutaFotoActual);
                }
            }

            $nombreFoto      = $file->getRandomName();
            $file->move(FCPATH . 'uploads/socios', $nombreFoto);
            $datos['url_foto'] = '/uploads/socios/' . $nombreFoto;
        }

        $this->model->update($id, $datos);

        return redirect()->to('/index.php/socios')->with('success', 'Socio actualizado correctamente.');
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

        return redirect()->to('/index.php/socios')->with('success', 'Socio eliminado correctamente.');
    }
}
