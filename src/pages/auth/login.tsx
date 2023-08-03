import { useState, useEffect, useRef } from 'react';
import { Toastr, ToastrRef, ToastrProps } from '@paljs/ui/Toastr';
import { useRouter } from 'next/router'
import { Button } from '@paljs/ui/Button';
import { InputGroup } from '@paljs/ui/Input';
import { Checkbox } from '@paljs/ui/Checkbox';
import React from 'react';
import Link from 'next/link';
import Spinner from '@paljs/ui/Spinner';
import Progress from '@paljs/ui/ProgressBar';

import Auth, { Group } from 'components/Auth';
import Socials from 'components/Auth/Socials';
import Layout from 'Layouts';

import { validateEmail, checkPassword } from 'src/helpers/hooks';
import { size } from 'lodash';
import CryptoJS from "crypto-js"
import Store from 'store'

export default function Login() {
  const router = useRouter()
  const toastrRef = useRef<ToastrRef>(null);
  const [values, setValues] = useState({email: '', password: ''})
  const [loading, setLoading] = useState(false)
  const [progress, setProgress] = useState(0)
  const [isError, setIsError] = useState(false)

  const onCheckbox = () => {
    // v will be true or false
  };

  useEffect(() => {
    if (size(router?.query?.token) > 0) {
      const token = router?.query?.token ?? ''

      if (token !== '') {
        setLoading(true)
        const _data = new FormData()
        _data.append('token', token)
        fetch(`${process.env.NEXT_PUBLIC_BE_API}user/activation`,
        {
          method: 'POST',
          body: _data
        })
        .then((res) => res.json())
        .then((res) => {
          toastrRef.current?.add(res?.message, (res?.status === '000') ? 'Success' : 'Danger', {
            position: 'topEnd',
            status: 'Success',
            duration: 3000,
            hasIcon: true,
            destroyByClick: true,
            preventDuplicates: false,
          })
          setLoading(false)
        }).catch(() => setLoading(false))
      }
    }
  }, [router?.query])

  const handleChange = prop => event => {
    setValues({ ...values, [prop]: event.target.value })
  }

  useEffect(() => {
    let _isError = 0
    if (!validateEmail(values?.email)) {
      _isError++
    }
    if (checkPassword(values?.password)?.error === 'Weak!') {
      _isError++
    }

    setProgress((2-_isError)/2*100)
    if (_isError > 0) {
      setIsError(true)
    } else {
      setIsError(false)
    }
    // console.log('values:', values);
  }, [values])

  const handleSubmit = (event:any) => {
    event.preventDefault()
    
    setLoading(true)
    const _data = new FormData()
    _data.append('email', values?.email.trim().toLocaleLowerCase())
    _data.append('password', values?.password.trim())
    fetch(`${process.env.NEXT_PUBLIC_BE_API}user/login`,
    {
      method: 'POST',
      body: _data
    })
    .then((res) => res.json())
    .then((res) => {
      if (res?.status === '000') { 
        toastrRef.current?.add(res?.message, 'Success', {
          position: 'topEnd',
          status: 'Success',
          duration: 3000,
          hasIcon: true,
          destroyByClick: true,
          preventDuplicates: false,
        })
        setValues({
          email: '',
          password: '',
        })
        const authUser = CryptoJS.AES.encrypt(`${JSON.stringify(res?.data)}`, `${process.env.NEXT_PUBLIC_SECRET_KEY}`).toString()
        Store.set('auth-user', authUser)
        Store.set('token', res?.data?.token)
        setTimeout(() => window.location.href='/', 3000)
      } else {
        toastrRef.current?.add(res?.message, 'Danger', {
          position: 'topEnd',
          status: 'Danger',
          duration: 3000,
          hasIcon: true,
          destroyByClick: true,
          preventDuplicates: false,
        })
      }

      setTimeout(() => setLoading(false), 3000)
    })
    .catch(() => setLoading(false))
  }

  useEffect(() => {
    if (
      Store.get('auth-user') &&
      CryptoJS.AES.decrypt(`${Store.get('auth-user')}`, `${process.env.NEXT_PUBLIC_SECRET_KEY}`).toString(CryptoJS.enc.Utf8)
    ) {
      window.location.href='/'
    }
  }, [])

  return (
    <Layout title="Login">
      <Auth title="Login" subTitle="Hello! Login with your email">
        <form onSubmit={e => handleSubmit(e)}>
          <InputGroup fullWidth status={!validateEmail(values?.email) ? 'Warning' : 'Success'}>
            <input type="email" onChange={handleChange('email')} placeholder="Email Address" />
          </InputGroup>
          <InputGroup fullWidth status={(checkPassword(values?.password)?.error === 'Weak!') ? 'Warning' : 'Success'}>
            <input type="password" onChange={handleChange('password')} placeholder="Password" />
          </InputGroup>
          <Group>
            <Checkbox checked onChange={onCheckbox}>
              Remember me
            </Checkbox>
            <Link href="/auth/request-password">
              <a>Forgot Password?</a>
            </Link>
          </Group>
          <Button status="Success" type="submit" shape="SemiRound" fullWidth disabled={isError || loading}>
            Login
          </Button>
          
          <Progress style={{ marginBottom: '1rem' }} value={progress} status="Info">
            Valid
          </Progress>
        </form>
        <Socials />
        <p>
          Don&apos;t have account?{' '}
          <Link href="/auth/register">
            <a>Register</a>
          </Link>
        </p>
      </Auth>
      <Toastr ref={toastrRef} />
      {loading && <Spinner size="Large" status="Danger" />}
    </Layout>
  );
}
