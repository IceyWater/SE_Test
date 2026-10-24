import InputError from '@/components/input-error';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { type FormEventHandler } from 'react';

type LoginForm = { identifier: string; password: string; remember: boolean };
type LoginProps = { status?: string; canResetPassword: boolean; googleEnabled: boolean };

export default function Login({ status, canResetPassword, googleEnabled }: LoginProps) {
    const googleError = usePage<{ errors: { google?: string } }>().props.errors.google;
    const { data, setData, post, processing, errors, reset } = useForm<LoginForm>({ identifier: '', password: '', remember: false });
    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        post(route('login'), { onFinish: () => reset('password') });
    };
    const inputClass =
        'mt-1.5 h-[50px] w-full rounded-[10px] border border-[#e4e4e7] bg-white px-5 text-[18px] text-[#111] placeholder:text-[#808080] outline-none transition focus:border-[#df2050] focus:ring-2 focus:ring-[#df2050]/15 disabled:opacity-60';

    return (
        <main className="flex min-h-svh items-center justify-center bg-[#f5f5f5] p-5 text-[#111] [color-scheme:light]">
            <Head title="Log in" />
            <div className="grid w-full max-w-[960px] overflow-hidden rounded-2xl border border-[#e9e9e9] bg-white shadow-[0_12px_18px_-8px_rgba(0,0,0,0.17)] md:grid-cols-2">
                <section className="px-6 py-10 sm:px-[60px] md:min-h-[680px] md:px-[60px] md:py-[50px]" aria-labelledby="login-title">
                    <header className="text-center">
                        <h1 id="login-title" className="text-[26px] leading-8 font-semibold tracking-[-0.6px]">
                            Graduate School System
                        </h1>
                        <p className="mt-2 text-[18px] leading-7 text-[#808080]">Login to your UIC account</p>
                    </header>
                    {status && (
                        <p role="status" className="mt-4 text-center text-sm text-green-700">
                            {status}
                        </p>
                    )}
                    <form onSubmit={submit} className="mt-[38px]">
                        <div>
                            <label htmlFor="identifier" className="block text-[18px] leading-6 font-medium">
                                Email or Student Number
                            </label>
                            <input
                                id="identifier"
                                name="identifier"
                                type="text"
                                autoComplete="username"
                                required
                                value={data.identifier}
                                onChange={(e) => setData('identifier', e.target.value)}
                                placeholder="Email or Student Number"
                                className={inputClass}
                                aria-invalid={!!errors.identifier}
                                aria-describedby={errors.identifier ? 'identifier-error' : undefined}
                            />
                            <div id="identifier-error">
                                <InputError message={errors.identifier} className="mt-1" />
                            </div>
                        </div>
                        <div className="mt-8">
                            <div className="flex items-center justify-between gap-2 text-[18px] leading-6">
                                <label htmlFor="password" className="font-medium">
                                    Password
                                </label>
                                {canResetPassword && (
                                    <Link
                                        href={route('password.request')}
                                        className="whitespace-nowrap underline decoration-[#d4d4d8] underline-offset-4 hover:text-[#df2050] focus-visible:outline-2 focus-visible:outline-offset-4"
                                    >
                                        Forgot password?
                                    </Link>
                                )}
                            </div>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autoComplete="current-password"
                                required
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="Password"
                                className={inputClass}
                                aria-invalid={!!errors.password}
                                aria-describedby={errors.password ? 'password-error' : undefined}
                            />
                            <div id="password-error">
                                <InputError message={errors.password} className="mt-1" />
                            </div>
                        </div>
                        <label htmlFor="remember" className="mt-[30px] flex w-fit cursor-pointer items-center gap-3.5 text-[18px] leading-6">
                            <input
                                id="remember"
                                name="remember"
                                type="checkbox"
                                checked={data.remember}
                                onChange={(e) => setData('remember', e.target.checked)}
                                className="h-5 w-5 cursor-pointer rounded border-[#e4e4e7] accent-[#df2050]"
                            />
                            Remember me
                        </label>
                        <button
                            type="submit"
                            disabled={processing}
                            className="mt-[46px] flex h-[45px] w-full items-center justify-center gap-2 rounded-[9px] bg-[#df2050] text-[18px] font-medium text-white transition hover:bg-[#c91c47] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#df2050] disabled:cursor-wait disabled:opacity-70"
                        >
                            {processing && <LoaderCircle className="h-4 w-4 animate-spin" aria-hidden="true" />}Log in
                        </button>
                    </form>
                    <div className="mt-8 flex items-center gap-3 text-[14px] leading-6 text-[#808080]" aria-hidden="true">
                        <span className="h-px flex-1 bg-[#dedede]" />
                        OR
                        <span className="h-px flex-1 bg-[#dedede]" />
                    </div>
                    {googleEnabled ? (
                        <a
                            href={route('auth.google.redirect')}
                            className="mt-8 flex h-[46px] w-full items-center justify-center rounded-[10px] border border-[#e4e4e7] bg-white text-[18px] font-medium text-[#111] shadow-sm transition hover:bg-[#fafafa] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#df2050]"
                        >
                            Continue with Google
                        </a>
                    ) : (
                        <button
                            type="button"
                            disabled
                            title="Google sign-in is currently unavailable"
                            className="mt-8 h-[46px] w-full rounded-[10px] border border-[#e4e4e7] bg-white text-[18px] font-medium text-[#808080] shadow-sm disabled:cursor-not-allowed"
                        >
                            Continue with Google
                        </button>
                    )}
                    {googleError && (
                        <div role="alert" className="mt-2">
                            <InputError message={googleError} />
                        </div>
                    )}
                </section>
                <div className="relative hidden bg-[#ff466b] md:block" aria-hidden="true">
                    <img src="/grad_logo.png" alt="" className="absolute inset-0 h-full w-full object-cover" />
                </div>
            </div>
        </main>
    );
}
