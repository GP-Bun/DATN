import React, { useEffect } from "react";
import api from "../api/api";

const TestApi: React.FC = () => {
  useEffect(() => {
    api.get("/user-profile")
      .then((res) => {
        console.log("✅ Kết nối thành công:", res.data);
      })
      .catch((err) => {
        console.error("❌ Lỗi kết nối:", err);
      });
  }, []);

  return (
    <div style={{ padding: 20 }}>
      <h1>Kiểm tra kết nối API Laravel</h1>
      <p>Mở console (F12 → Console) để xem kết quả.</p>
    </div>
  );
};

export default TestApi;
