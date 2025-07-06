/**
 * Telegram Settings Component for Xboard Admin Panel
 * This React component can be integrated into the admin dashboard
 */

import React, { useState, useEffect } from 'react';
import { Card, Form, Input, Switch, Button, message, Statistic, Row, Col, Alert, Space } from 'antd';
import { TelegramOutlined, CheckCircleOutlined, ExclamationCircleOutlined } from '@ant-design/icons';

const { TextArea } = Input;

const TelegramSettings = () => {
  const [form] = Form.useForm();
  const [loading, setLoading] = useState(false);
  const [testLoading, setTestLoading] = useState(false);
  const [config, setConfig] = useState({});
  const [stats, setStats] = useState({});

  useEffect(() => {
    loadConfiguration();
    loadStats();
  }, []);

  const loadConfiguration = async () => {
    try {
      const response = await fetch('/api/v2/admin/telegram/config', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
          'Accept': 'application/json'
        }
      });
      
      if (response.ok) {
        const result = await response.json();
        setConfig(result.data);
        form.setFieldsValue(result.data);
      }
    } catch (error) {
      message.error('Failed to load telegram configuration');
    }
  };

  const saveConfiguration = async (values) => {
    setLoading(true);
    try {
      const response = await fetch('/api/v2/admin/telegram/config', {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(values)
      });

      if (response.ok) {
        message.success('Telegram configuration saved successfully');
        loadConfiguration();
      } else {
        const result = await response.json();
        message.error('Failed to save configuration: ' + result.message);
      }
    } catch (error) {
      message.error('Failed to save configuration');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ padding: 24 }}>
      <Card title="Telegram Integration">
        <Form
          form={form}
          layout="vertical"
          onFinish={saveConfiguration}
        >
          <Form.Item label="Bot Token" name="bot_token">
            <Input.Password placeholder="1234567890:ABCdefGHIjklMNOpqrsTUVwxyz" />
          </Form.Item>
          
          <Form.Item label="Bot Username" name="bot_username">
            <Input placeholder="your_bot_username" />
          </Form.Item>

          <Form.Item name="login_enabled" valuePropName="checked">
            <Switch /> Enable Telegram Login
          </Form.Item>

          <Form.Item name="signup_enabled" valuePropName="checked">
            <Switch /> Enable Telegram Signup
          </Form.Item>

          <Form.Item>
            <Button type="primary" htmlType="submit" loading={loading}>
              Save Configuration
            </Button>
          </Form.Item>
        </Form>
      </Card>
    </div>
  );
};

export default TelegramSettings;
