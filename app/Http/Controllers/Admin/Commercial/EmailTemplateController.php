<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'approved']);
    }

    public function index()
    {
        $templates = EmailTemplate::orderBy('key')->paginate(20);
        return view('admin.commercial.emails.index', compact('templates'));
    }

    public function create()
    {
        $template = new EmailTemplate(['is_active' => true]);
        // Reutiliza a mesma view de edição para criação
        return view('admin.commercial.emails.edit', compact('template'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:255', 'unique:email_templates,key'],
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'footer' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $tpl = EmailTemplate::create($data);
        return redirect()->route('admin.commercial.email-templates.edit', $tpl)
            ->with('status', 'Template criado.');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        return view('admin.commercial.emails.edit', [
            'template' => $emailTemplate,
        ]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:255', 'unique:email_templates,key,' . $emailTemplate->id],
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'footer' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $emailTemplate->update($data);
        return back()->with('status', 'Template atualizado.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();
        return redirect()->route('admin.commercial.email-templates.index')
            ->with('status', 'Template removido.');
    }

    public function preview(Request $request, EmailTemplate $emailTemplate)
    {
        $vars = $request->input('vars', []);
        $subject = $emailTemplate->renderSubject($vars);
        $body = $emailTemplate->render($vars); // HTML bruto, sem escape

        // Suporta JSON quando solicitado
        if ($request->wantsJson() || $request->query('format') === 'json') {
            return response()->json([
                'subject' => $subject,
                'body' => $body,
            ]);
        }

        // Resposta HTML simples para visualização direta no navegador
        $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8">'
              . '<meta name="viewport" content="width=device-width, initial-scale=1">'
              . '<title>' . e($subject) . '</title>'
              . '<style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,\n Noto Sans,\n Oxygen,\n Cantarell,\n Helvetica Neue,Arial,sans-serif; background:#f5f5f5; padding:24px;} .box{max-width:760px;margin:0 auto;background:#fff;padding:24px;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,.06);} .subject{font-size:18px;font-weight:600;color:#111;margin-bottom:12px}</style>'
              . '</head><body><div class="box">'
              . '<div class="subject">' . e($subject) . '</div>'
              . $body
              . '</div></body></html>';

        return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
    }
}
