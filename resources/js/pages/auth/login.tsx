import { Head, useForm, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

type LoginForm = {
    identifier: string; // email or student number
    password: string;
    remember: boolean;
};

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
    googleEnabled: boolean;
}

export default function Login(props: LoginProps) {
    const { status, canResetPassword, googleEnabled } = props;
    const googleError = usePage<{ errors: { google?: string } }>().props.errors.google;
    const { data, setData, post, processing, errors, reset } = useForm<Required<LoginForm>>({
        identifier: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onSuccess: () => {
                reset('password');
            },
            onError: () => {
                reset('password');
            },
        });
    };

    return (
        <AuthLayout title="Graduate School System" description="Login to your UIC account">
            <Head title="Log in" />

            {status && <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>}

            <div className="mb-6 space-y-3">
                {googleEnabled ? (
                    <Button asChild variant="outline" className="w-full">
                        <a href={route('auth.google.redirect')}>
                            <svg viewBox="0 0 24 24" className="size-4" aria-hidden="true">
                                <path fill="#4285F4" d="M21.6 12.23c0-.71-.06-1.39-.18-2.05H12v3.88h5.38a4.6 4.6 0 0 1-2 3.02v2.51h3.24c1.9-1.75 2.98-4.33 2.98-7.36Z" />
                                <path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.62-2.41l-3.24-2.51c-.9.6-2.05.97-3.38.97-2.6 0-4.81-1.76-5.6-4.13H3.06v2.59A10 10 0 0 0 12 22Z" />
                                <path fill="#FBBC05" d="M6.4 13.92A6 6 0 0 1 6.09 12c0-.67.11-1.32.31-1.92V7.49H3.06A10 10 0 0 0 2 12c0 1.61.38 3.14 1.06 4.51l3.34-2.59Z" />
                                <path fill="#EA4335" d="M12 5.95c1.47 0 2.79.51 3.83 1.51l2.87-2.87A9.6 9.6 0 0 0 12 2a10 10 0 0 0-8.94 5.49l3.34 2.59c.79-2.37 3-4.13 5.6-4.13Z" />
                            </svg>
                            Sign in with Google
                        </a>
                    </Button>
                ) : (
                    <Button variant="outline" className="w-full" disabled>Sign in with Google</Button>
                )}
                <p className="text-center text-xs text-muted-foreground">
                    {googleEnabled ? 'Use the @uic.edu.ph Google account linked to your system account.' : 'Google sign-in is currently unavailable. Use your account credentials below.'}
                </p>
                <div role="alert"><InputError message={googleError} /></div>
                <div className="relative py-2 text-center text-xs text-muted-foreground">
                    <span className="bg-background relative z-10 px-3">or sign in with your credentials</span>
                    <div className="absolute inset-x-0 top-1/2 border-t" />
                </div>
            </div>

            <form className="flex flex-col gap-6" onSubmit={submit}>
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="identifier">Email or Student Number</Label>
                        <Input
                            id="identifier"
                            name="identifier"
                            type="text"
                            required
                            autoComplete="username"
                            value={data.identifier}
                            onChange={e => setData('identifier', e.target.value)}
                            placeholder="Email or Student Number"
                        />
                        <InputError message={errors.identifier} />
                    </div>

                    <div className="grid gap-2">
                        <div className="flex items-center">
                            <Label htmlFor="password">Password</Label>
                            {canResetPassword && (
                                <TextLink href={route('password.request')} className="ml-auto text-sm" tabIndex={5}>
                                    Forgot password?
                                </TextLink>
                            )}
                        </div>
                        <Input
                            id="password"
                            type="password"
                            required
                            tabIndex={2}
                            autoComplete="current-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="Password"
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div className="flex items-center space-x-3">
                        <Checkbox
                            id="remember"
                            name="remember"
                            checked={data.remember}
                            onClick={() => setData('remember', !data.remember)}
                            tabIndex={3}
                        />
                        <Label htmlFor="remember">Remember me</Label>
                    </div>

                    <Button type="submit" className="mt-4 w-full" tabIndex={4} disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Log in
                    </Button>
                </div>
            </form>
        </AuthLayout>
    );
}
