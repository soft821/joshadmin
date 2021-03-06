<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Repositories\CompanyRepository;
use App\Http\Controllers\AppBaseController as InfyOmBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\Models\Company;
use Flash;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

use Sentinel;

class CompanyController extends InfyOmBaseController
{
    /** @var  CompanyRepository */
    private $companyRepository;

    public function __construct(CompanyRepository $companyRepo)
    {
        $this->companyRepository = $companyRepo;
    }

    /**
     * Display a listing of the Company.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {

        $this->companyRepository->pushCriteria(new RequestCriteria($request));
        if (Sentinel::check()) {
            //Activity log
            $user = Sentinel::getuser();
            $idCompany = $user->idCompany;
        }
        // $companies = $this->companyRepository->all();
        $company = $this->companyRepository->findWithoutFail($idCompany);

        return view('admin.companies.index')
            ->with('company', $company);
    }

    /**
     * Show the form for creating a new Company.
     *
     * @return Response
     */
    public function create()
    {
        return view('admin.companies.create');
    }

    /**
     * Store a newly created Company in storage.
     *
     * @param CreateCompanyRequest $request
     *
     * @return Response
     */
    public function store(CreateCompanyRequest $request)
    {
        $input = $request->all();

        $company = $this->companyRepository->create($input);
        $idCompany = Company::all() -> last() -> idCompany;
        if (Sentinel::check()) {
            //Activity log
            $user = Sentinel::getuser();
            $user->idCompany = $idCompany;
            $user->save();

        }

        Flash::success('Company saved successfully.');

        return redirect(route('admin.companies.index'));
    }

    /**
     * Display the specified Company.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $company = $this->companyRepository->findWithoutFail($id);
        // dd($company);

        if (empty($company)) {
            Flash::error('Company not found');

            return redirect(route('companies.index'));
        }

        return view('admin.companies.show')->with('company', $company);
    }

    /**
     * Show the form for editing the specified Company.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $company = $this->companyRepository->findWithoutFail($id);

        if (empty($company)) {
            Flash::error('Company not found');

            return redirect(route('companies.index'));
        }

        return view('admin.companies.edit')->with('company', $company);
    }

    /**
     * Update the specified Company in storage.
     *
     * @param  int              $id
     * @param UpdateCompanyRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateCompanyRequest $request)
    {
        $company = $this->companyRepository->findWithoutFail($id);

        

        if (empty($company)) {
            Flash::error('Company not found');

            return redirect(route('companies.index'));
        }

        $company = $this->companyRepository->update($request->all(), $id);

        Flash::success('Company updated successfully.');

        return redirect(route('admin.companies.index'));
    }

    /**
     * Remove the specified Company from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
      public function getModalDelete($id = null)
      {
          $error = '';
          $model = '';
          $confirm_route =  route('admin.companies.delete',['id'=>$id]);
          return View('admin.layouts/modal_confirmation', compact('error','model', 'confirm_route'));

      }

       public function getDelete($id = null)
       {
           $sample = Company::destroy($id);

           // Redirect to the group management page
           return redirect(route('admin.companies.index'))->with('success', Lang::get('message.success.delete'));

       }

}
