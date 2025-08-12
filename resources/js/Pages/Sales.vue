<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import DangerButton from "@/Components/DangerButton.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import WarningButton from "@/Components/WarningButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";
import Swal from "sweetalert2";
import { nextTick, ref, computed, watch } from "vue";

const nameInput = ref(null);
const modal = ref(false);
const title = ref("");
const operation = ref(1);
const id = ref("");

const props = defineProps({
    sales: { type: Object },
    inventories: { type: Object },
});
const form = useForm({
    product: "",
    quantity: "",
    price: "",
    customer_name: "",
    customer_email: "",
    customer_address: "",
    status: "Pending",
});
const formPage = useForm({});
const onPageClick = (event) => {
    formPage.get(route("sales.index", { page: event }));
};
const openModal = (op, product, quantity, price, status, sale) => {
    modal.value = true;
    nextTick(() => nameInput.value.focus());
    operation.value = op;
    id.value = sale;
    if (op == 1) {
        title.value = "Add Entry";
    } else {
        title.value = "Edit Entry";
        form.product = product;
        form.quantity = quantity;
        form.price = price;
        form.customer_name = customer_name;
        form.customer_email = customer_email;
        form.customer_address = customer_address;
        form.status = status;
    }
};
const closeModal = () => {
    modal.value = false;
    form.reset();
};
const save = () => {
    if (operation.value == 1) {
        form.post(route("sales.store"), {
            onSuccess: () => {
                ok("Entry Added");
            },
        });
    } else {
        form.put(route("sales.update", id.value), {
            onSuccess: () => {
                ok("Entry updated");
            },
        });
    }
};
const ok = (msj) => {
    form.reset();
    closeModal();
    Swal.fire({ title: msj, icon: "success" });
};
const deleteSale = (sale) => {
    const alerta = Swal.mixin({
        buttonStyling: true,
    });
    alerta
        .fire({
            title: "This action will delete " + sale.product,
            icon: "exclamation",
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-check"></i> Confirm',
            cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancel',
        })
        .then((result) => {
            if (result.isConfirmed) {
                form.delete(route("sales.destroy", sale), {
                    onSuccess: () => {
                        ok("Entry deleted");
                    },
                });
            }
        });
};

const formatToIDR = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};

const productMap = computed(() => {
    const map = {};
    props.inventories.forEach((product) => {
        map[product.name] = product.sell_price;
    });
    return map;
});

watch(
    () => [form.product, form.quantity],
    ([product, quantity]) => {
        const basePrice = productMap.value[product] || 0;
        const qty = parseInt(quantity) || 0;
        form.price = basePrice * qty;
    }
);

const updateStatus = (sale, newStatus) => {
    Swal.fire({
        title: `Change status to ${newStatus}?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes",
        cancelButtonText: "No",
    }).then((result) => {
        if (result.isConfirmed) {
            const statusForm = useForm({
                product: sale.product,
                quantity: sale.quantity,
                price: sale.price,
                status: newStatus,
            });

            statusForm.put(route("sales.update", sale.id), {
                onSuccess: () => {
                    sale.status = newStatus;
                    Swal.fire({
                        title: `${newStatus} successfully!`,
                        icon: "success",
                    });
                },
                onError: () => {
                    Swal.fire({
                        title: "Error updating status",
                        icon: "error",
                    });
                },
            });
        }
    });
};

const dateSortOrder = ref("asc");

const toggleDateSort = () => {
    dateSortOrder.value = dateSortOrder.value === "asc" ? "desc" : "asc";
};

const startDate = ref(null); // format: '2025-08-01'
const endDate = ref(null);
const searchQuery = ref("");
const statusFilter = ref("");

const filteredSales = computed(() => {
    return props.sales
        .filter((sale) => {
            const createdAt = new Date(sale.created_at);
            const query = searchQuery.value.toLowerCase();

            const matchesSearch =
                sale.product.toLowerCase().includes(query) ||
                sale.status.toLowerCase().includes(query) ||
                sale.so_number?.toLowerCase().includes(query) || // jika ada so_number
                createdAt.toLocaleDateString().includes(searchQuery.value);

            const matchesStatus =
                statusFilter.value === "" || sale.status === statusFilter.value;

            const matchesDate =
                (!startDate.value || createdAt >= new Date(startDate.value)) &&
                (!endDate.value ||
                    createdAt <= new Date(endDate.value + "T23:59:59"));

            return matchesSearch && matchesStatus && matchesDate;
        })
        .sort((a, b) => {
            const dateA = new Date(a.created_at);
            const dateB = new Date(b.created_at);
            return dateSortOrder.value === "asc"
                ? dateA - dateB
                : dateB - dateA;
        });
});

// ✅ Tambahkan reset function untuk digunakan di UI jika perlu
function resetFilters() {
    startDate.value = null;
    endDate.value = null;
    searchQuery.value = "";
    statusFilter.value = "";
}

const statusColors = {
    Pending: "bg-yellow-100 text-yellow-800",
    Approved: "bg-blue-100 text-blue-800",
    Paid: "bg-indigo-100 text-indigo-800",
    Shipped: "bg-purple-100 text-purple-800",
    Received: "bg-green-100 text-green-800",
    Completed: "bg-gray-100 text-gray-800",
    Canceled: "bg-red-100 text-red-800",
    Refunded: "bg-pink-100 text-pink-800",
};

// Upload Paid Modal
const paidModal = ref(false);
const selectedSale = ref(null);
const paymentFile = ref(null);
const paymentProofDesc = ref("");

const openPaidModal = (sale) => {
    selectedSale.value = sale;
    paidModal.value = true;
};

const closePaidModal = () => {
    paidModal.value = false;
    paymentFile.value = null;
};

const onFileChange = (event) => {
    paymentFile.value = event.target.files[0];
};

const submitPaid = () => {
    if (!paymentFile.value) {
        Swal.fire("Error", "Please upload a payment proof first.", "error");
        return;
    }

    const formData = new FormData();
    formData.append("product", selectedSale.value.product);
    formData.append("quantity", selectedSale.value.quantity);
    formData.append("price", selectedSale.value.price);
    formData.append("status", "Paid");
    formData.append("payment_proof_so", paymentFile.value);

    // kirim deskripsi
    formData.append("payment_proof_desc", paymentProofDesc.value);

    axios.post(route("sales.update", selectedSale.value.id), formData, {
        headers: { "Content-Type": "multipart/form-data" },
        method: "POST",
        params: { _method: "PUT" }
    })
    .then(() => {
        closePaidModal();
        Swal.fire("Success", "Sale marked as Paid!", "success").then(() => {
            location.reload();
        });
    })
    .catch(err => {
        Swal.fire("Error", err.response?.data?.message || "Upload failed", "error");
    });
};

const shipModal = ref(false);
const selectedShipSale = ref(null);
const shippingFile = ref(null);
const shippingProofDesc = ref("");

const openShipModal = (sale) => {
    selectedShipSale.value = sale;
    shipModal.value = true;
};

const closeShipModal = () => {
    shipModal.value = false;
    shippingFile.value = null;
};

const onShipFileChange = (event) => {
    shippingFile.value = event.target.files[0];
};

const submitShip = () => {
    if (!shippingFile.value) {
        Swal.fire("Error", "Please upload a shipping proof first.", "error");
        return;
    }

    const formData = new FormData();
    formData.append("product", selectedShipSale.value.product);
    formData.append("quantity", selectedShipSale.value.quantity);
    formData.append("price", selectedShipSale.value.price);
    formData.append("status", "Shipped");
    formData.append("shipping_proof_so", shippingFile.value);

    // ✅ kirim deskripsi
    formData.append("shipping_proof_desc", shippingProofDesc.value);

    axios.post(route("sales.update", selectedShipSale.value.id), formData, {
        headers: { "Content-Type": "multipart/form-data" },
        method: "POST",
        params: { _method: "PUT" }
    })
    .then(() => {
        closeShipModal();
        Swal.fire("Success", "Sale marked as Shipped!", "success").then(() => {
            location.reload();
        });
    })
    .catch(err => {
        Swal.fire("Error", err.response?.data?.message || "Upload failed", "error");
    });
};

const receiveModal = ref(false);
const selectedReceiveSale = ref(null);
const receivedFile = ref(null);
const receivedProofDesc = ref("");

const openReceiveModal = (sale) => {
    selectedReceiveSale.value = sale;
    receiveModal.value = true;
};

const closeReceiveModal = () => {
    receiveModal.value = false;
    receivedFile.value = null;
    receivedProofDesc.value = "";
};

const onReceiveFileChange = (event) => {
    receivedFile.value = event.target.files[0];
};

const submitReceive = () => {
    if (!receivedFile.value) {
        Swal.fire("Error", "Please upload a received proof first.", "error");
        return;
    }

    const formData = new FormData();
    formData.append("product", selectedReceiveSale.value.product);
    formData.append("quantity", selectedReceiveSale.value.quantity);
    formData.append("price", selectedReceiveSale.value.price);
    formData.append("status", "Received");
    formData.append("received_proof_so", receivedFile.value);
    formData.append("received_proof_desc", receivedProofDesc.value);

    axios.post(route("sales.update", selectedReceiveSale.value.id), formData, {
        headers: { "Content-Type": "multipart/form-data" },
        method: "POST",
        params: { _method: "PUT" }
    })
    .then(() => {
        closeReceiveModal();
        Swal.fire("Success", "Sale marked as Received!", "success").then(() => {
            location.reload();
        });
    })
    .catch(err => {
        Swal.fire("Error", err.response?.data?.message || "Upload failed", "error");
    });
};


const detailModal = ref(false);
const detailData = ref(null);

const openDetailModal = (purchase) => {
    detailData.value = purchase;
    detailModal.value = true;
};

const closeDetailModal = () => {
    detailModal.value = false;
    detailData.value = null;
};



</script>

<template>
    <Head title="Sales" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Sales Module
            </h2>
        </template>

        <div
            class="py-12"
            v-if="
                $page.props.auth.user.role === 'Sales' ||
                $page.props.auth.user.role === 'Admin'
            "
        >
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div
                        class="p-6 text-gray-900"
                        v-if="$page.props.auth.user.role === 'Sales'"
                    >
                        Welcome, You're logged in as Sales
                    </div>
                    <div class="p-6 text-gray-900">
                        <PrimaryButton @click="($event) => openModal(1)">
                            Add
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <div
                class="m-6 flex flex-wrap gap-4 items-center bg-white p-4 rounded-lg shadow-md max-w-xl mx-auto"
            >
                <input
                    type="date"
                    v-model="startDate"
                    class="input input-bordered"
                />
                <input
                    type="date"
                    v-model="endDate"
                    class="input input-bordered"
                />
                <button @click="resetFilters" class="btn btn-outline btn-error">
                    Reset Filter
                </button>

                <input
                    type="text"
                    placeholder="Search by product, status, or date..."
                    v-model="searchQuery"
                    class="flex-grow min-w-[200px] border border-gray-300 rounded-lg px-4 py-2 text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                />

                <select
                    v-model="statusFilter"
                    class="w-40 border border-gray-300 rounded-lg px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                >
                    <option value="">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Paid">Paid</option>
                    <option value="Shipped">Shipped</option>
                    <option value="Received">Received</option>
                    <option value="Completed">Completed</option>
                    <option value="Canceled">Canceled</option>
                    <option value="Refunded">Refunded</option>
                </select>
            </div>

            <div class="mt-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto my-6 shadow rounded-lg">
                    <table class="min-w-full text-sm text-left text-gray-600">
                        <thead
                            class="bg-gray-100 text-xs uppercase text-gray-600 border-b border-gray-300"
                        >
                            <tr>
                                <th scope="col" class="px-4 py-3">-</th>
                                <th scope="col" class="px-4 py-3">No</th>
                                <th
                                    scope="col"
                                    class="px-4 py-3 cursor-pointer select-none"
                                    @click="toggleDateSort"
                                >
                                    Date
                                    <span v-if="dateSortAsc">▲</span>
                                    <span v-else>▼</span>
                                </th>
                                <th scope="col" class="px-4 py-3">Product</th>
                                <th scope="col" class="px-4 py-3 text-center">
                                    Quantity
                                </th>
                                <th scope="col" class="px-4 py-3 text-right">
                                    Price
                                </th>
                                <th scope="col" class="px-4 py-3">
                                    Customer Name
                                </th>
                                <th scope="col" class="px-4 py-3">
                                    Customer Email
                                </th>
                                <th scope="col" class="px-4 py-3">
                                    Customer Address
                                </th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3"></th>
                                <th scope="col" class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(inv, i) in filteredSales"
                                :key="inv.id"
                                :class="[
                                    'bg-white border-b last:border-none hover:bg-gray-50 transition',
                                    inv.status === 'Deleted'
                                        ? 'opacity-50 line-through'
                                        : '',
                                ]"
                            >
                                 <td class="px-4 py-3 flex justify-center">
                                    <PrimaryButton
                                        class="px-2 py-1 text-xs"
                                        @click="openDetailModal(inv)"
                                    >
                                        📄 Details
                                    </PrimaryButton>
                                </td>
                                <td class="px-4 py-3 font-mono text-sm">
                                    {{ inv.so_number ?? "N/A" }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{
                                        new Date(
                                            inv.created_at
                                        ).toLocaleDateString()
                                    }}
                                </td>
                                <th
                                    scope="row"
                                    class="px-4 py-3 font-semibold text-gray-900 whitespace-nowrap"
                                >
                                    {{ inv.product }}
                                </th>
                                <td class="px-4 py-3 text-center">
                                    {{ inv.quantity }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono">
                                    {{ formatToIDR(inv.price) }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ inv.customer_name }}
                                </td>
                                <td class="px-4 py-3 break-words max-w-xs">
                                    {{ inv.customer_email }}
                                </td>
                                <td class="px-4 py-3 max-w-sm">
                                    <textarea
                                        readonly
                                        class="resize w-full rounded-md border border-gray-300 px-2 py-1 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
                                        rows="1"
                                        @focus="$event.target.rows = 4"
                                        @blur="$event.target.rows = 1"
                                        >{{ inv.customer_address }}</textarea
                                    >
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
                                            statusColors[inv.status],
                                        ]"
                                    >
                                        {{ inv.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <template v-if="inv.status === 'Pending'">
                                        <PrimaryButton
                                            class="px-3 py-1 text-sm"
                                            @click="
                                                () => {
                                                    const matchedProduct =
                                                        props.inventories.find(
                                                            (p) =>
                                                                p.name ===
                                                                inv.product
                                                        );
                                                    if (!matchedProduct) {
                                                        Swal.fire({
                                                            icon: 'error',
                                                            title: 'Product not found',
                                                            text: 'Cannot approve because product is missing in inventory.',
                                                        });
                                                        return;
                                                    }
                                                    if (
                                                        matchedProduct.base_qty ===
                                                            0 ||
                                                        matchedProduct.base_qty <
                                                            inv.quantity
                                                    ) {
                                                        Swal.fire({
                                                            icon: 'error',
                                                            title: 'Cannot Approve',
                                                            text: `Product base quantity (${matchedProduct.base_qty}) is insufficient to fulfill demand (${inv.quantity}). Approval denied.`,
                                                        });
                                                        return;
                                                    }
                                                    updateStatus(
                                                        inv,
                                                        'Approved'
                                                    );
                                                }
                                            "
                                        >
                                            ✔ Approve
                                        </PrimaryButton>
                                        <DangerButton
                                            class="ml-2 px-3 py-1 text-sm"
                                            @click="
                                                () =>
                                                    updateStatus(
                                                        inv,
                                                        'Canceled'
                                                    )
                                            "
                                        >
                                            ✖ Cancel
                                        </DangerButton>
                                    </template>

                                    <template
                                        v-else-if="inv.status === 'Approved'"
                                    >
                                        <PrimaryButton
                                            class="px-3 py-1 text-sm"
                                            @click="() => openPaidModal(inv)"
                                        >
                                            💰 Paid
                                        </PrimaryButton>

                                        <DangerButton
                                            class="ml-2 px-3 py-1 text-sm"
                                            @click="
                                                () =>
                                                    updateStatus(
                                                        inv,
                                                        'Canceled'
                                                    )
                                            "
                                            >✖ Cancel</DangerButton
                                        >
                                    </template>

                                    <template v-else-if="inv.status === 'Paid'">
                                    <PrimaryButton
                                        class="px-3 py-1 text-sm"
                                        @click="() => openShipModal(inv)"
                                    >
                                        🚚 Ship
                                    </PrimaryButton>

                                        <DangerButton
                                            class="ml-2 px-3 py-1 text-sm"
                                            @click="
                                                () =>
                                                    updateStatus(
                                                        inv,
                                                        'Canceled'
                                                    )
                                            "
                                            >✖ Cancel
                                        </DangerButton>
                                    </template>

                                    <template v-else-if="inv.status === 'Shipped'">
                                        <PrimaryButton
                                            class="px-3 py-1 text-sm"
                                            @click="() => openReceiveModal(inv)"
                                        >
                                            📦 Received
                                        </PrimaryButton>
                                    </template>

                                    <template
                                        v-else-if="inv.status === 'Received'"
                                    >
                                        <PrimaryButton
                                            class="px-3 py-1 text-sm"
                                            @click="
                                                () =>
                                                    updateStatus(
                                                        inv,
                                                        'Completed'
                                                    )
                                            "
                                            >✅ Complete</PrimaryButton
                                        >
                                        <DangerButton
                                            class="ml-2 px-3 py-1 text-sm"
                                            @click="
                                                () =>
                                                    updateStatus(
                                                        inv,
                                                        'Refunded'
                                                    )
                                            "
                                            >↩ Refund</DangerButton
                                        >
                                    </template>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <DangerButton
                                        v-if="
                                            !(
                                                inv.status === 'Deleted' ||
                                                inv.status === 'Approved' ||
                                                inv.status === 'Paid' ||
                                                inv.status === 'Shipped' ||
                                                inv.status === 'Received'
                                            )
                                        "
                                        class="ml-2 px-3 py-1 text-sm"
                                        @click="
                                            () => updateStatus(inv, 'Deleted')
                                        "
                                    >
                                        Delete
                                    </DangerButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="py-12" v-else>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        You're not authorized here...
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="detailModal" @close="closeDetailModal">
            <h2 class="p-3 text-lg font-medium text-gray-900">Sales Detail</h2>
            <div v-if="detailData" class="p-4 space-y-3 text-sm text-gray-700">
                <p><strong>SO Number:</strong> {{ detailData.so_number ?? '-' }}</p>
                <p><strong>Date:</strong> {{ new Date(detailData.created_at).toLocaleDateString() }}</p>
                <p><strong>Product:</strong> {{ detailData.product }}</p>
                <p><strong>Quantity:</strong> {{ detailData.quantity }}</p>
                <p><strong>Price:</strong> {{ formatToIDR(detailData.price) }}</p>
                <p><strong>Status:</strong> {{ detailData.status }}</p>

                <hr class="my-2">

                <p><strong>Customer Name:</strong> {{ detailData.customer_name }}</p>
                <p><strong>Customer Email:</strong> {{ detailData.customer_email }}</p>
                <p><strong>Customer Address:</strong> {{ detailData.customer_address }}</p>

                <hr class="my-2">

                <div v-if="detailData.payment_proof_so">
                    <p><strong>Payment Proof:</strong></p>
                    <img
                        :src="`/storage/payment_proof_so/${detailData.payment_proof_so}`"
                        alt="Payment Proof"
                        class="max-w-full rounded border"
                    >

                    <!-- Tambahan: Deskripsi -->
                    <p class="mt-2 text-gray-700">
                        <strong>Description:</strong> {{ detailData.payment_proof_desc || '-' }}
                    </p>
                </div>
                <div v-else>
                    <p><strong>Payment Proof:</strong> -</p>
                </div>

                <hr class="my-2">

                <div v-if="detailData.shipping_proof_so">
                    <p><strong>Shipping Proof:</strong></p>
                    <img
                        :src="`/storage/shipping_proof_so/${detailData.shipping_proof_so}`"
                        alt="Shipping Proof"
                        class="max-w-full rounded border"
                    >

                    <p class="mt-2 text-gray-700">
                        <strong>Description:</strong> {{ detailData.shipping_proof_desc || '-' }}
                    </p>
                </div>
                <div v-else>
                    <p><strong>Shipping Proof:</strong> -</p>
                </div>

                <hr class="my-2">

                <div v-if="detailData.received_proof_so">
                    <p><strong>Received Proof:</strong></p>
                    <img
                        :src="`/storage/received_proof_so/${detailData.received_proof_so}`"
                        alt="Received Proof"
                        class="max-w-full rounded border"
                    >
                    <p class="mt-2 text-gray-700">
                        <strong>Description:</strong> {{ detailData.received_proof_desc || '-' }}
                    </p>
                </div>
                <div v-else>
                    <p><strong>Received Proof:</strong> -</p>
                </div>


            </div>

            <div class="p-3 mt-4 text-right">
                <PrimaryButton @click="closeDetailModal">
                    Close
                </PrimaryButton>
            </div>
        </Modal>

        <Modal :show="shipModal" @close="closeShipModal">
            <h2 class="p-3 text-lg font-medium text-gray-900">Upload Shipping Proof</h2>

            <div class="p-3 mt-6">
                <input
                    id="shipping_proof_so"
                    type="file"
                    class="mt-1 block w-3/4"
                    @change="onShipFileChange"
                />
            </div>

            <!-- ✅ Input deskripsi -->
            <div class="p-3 mt-6">
                <InputLabel for="shipping_proof_desc" value="Description"></InputLabel>
                <TextInput
                    id="shipping_proof_desc"
                    v-model="shippingProofDesc"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Enter shipping proof description"
                />
            </div>

            <div class="p-3 mt-6">
                <PrimaryButton @click="submitShip">
                    Save Shipping Proof
                </PrimaryButton>
            </div>
        </Modal>



        <Modal :show="paidModal" @close="closePaidModal">
            <h2 class="p-3 text-lg font-medium text-gray-900">Upload Payment Proof</h2>

            <div class="p-3 mt-6">
                <input
                    id="payment_proof_so"
                    type="file"
                    class="mt-1 block w-3/4"
                    @change="onFileChange"
                />
            </div>

            <!-- Tambahan: Input untuk deskripsi -->
            <div class="p-3 mt-6">
                <InputLabel for="payment_proof_desc" value="Description"></InputLabel>
                <TextInput
                    id="payment_proof_desc"
                    v-model="paymentProofDesc"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Enter payment proof description"
                />
            </div>

            <div class="p-3 mt-6">
                <PrimaryButton @click="submitPaid">
                    Save Payment
                </PrimaryButton>
            </div>
        </Modal>

        <Modal :show="receiveModal" @close="closeReceiveModal">
            <h2 class="p-3 text-lg font-medium text-gray-900">Upload Received Proof</h2>

            <div class="p-3 mt-6">
                <input
                    id="received_proof_so"
                    type="file"
                    class="mt-1 block w-3/4"
                    @change="onReceiveFileChange"
                />
            </div>

            <div class="p-3 mt-6">
                <InputLabel for="received_proof_desc" value="Description"></InputLabel>
                <TextInput
                    id="received_proof_desc"
                    v-model="receivedProofDesc"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Enter received proof description"
                />
            </div>

            <div class="p-3 mt-6">
                <PrimaryButton @click="submitReceive">
                    Save Received Proof
                </PrimaryButton>
            </div>
        </Modal>




        <Modal :show="modal" @close="closeModal">
            <h2 class="p-3 text-lg font.medium text-gray-900">{{ title }}</h2>
            <div class="p-3 mt-6">
                <InputLabel for="product" value="Product"></InputLabel>
                <select
                    id="product"
                    ref="nameInput"
                    v-model="form.product"
                    class="mt-1 block w-3/4 rounded-md border-gray-300 shadow-sm focus:ring focus:ring-indigo-200"
                >
                    <option value="" disabled>Select a product</option>
                    <option
                        v-for="product in props.inventories"
                        :key="product.id"
                        :value="product.name"
                    >
                        {{ product.name }}
                    </option>
                </select>
                <InputError
                    :message="form.errors.product"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3">
                <InputLabel for="quantity" value="Quantity"></InputLabel>
                <TextInput
                    id="quantity"
                    ref="nameInput"
                    v-model="form.quantity"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Quantity"
                ></TextInput>
                <InputError
                    :message="form.errors.quantity"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3">
                <InputLabel for="price" value="Price"></InputLabel>
                <TextInput
                    id="price"
                    ref="nameInput"
                    v-model="form.price"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Price"
                    :readonly="true"
                />
                <InputError
                    :message="form.errors.price"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3 mt-6">
                <InputLabel
                    for="customer_name"
                    value="Customer Name"
                ></InputLabel>
                <TextInput
                    id="customer_name"
                    ref="nameInput"
                    v-model="form.customer_name"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Customer Name"
                ></TextInput>
                <InputError
                    :message="form.errors.customer_name"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3 mt-6">
                <InputLabel
                    for="customer_email"
                    value="Customer Email"
                ></InputLabel>
                <TextInput
                    id="customer_email"
                    v-model="form.customer_email"
                    type="email"
                    class="mt-1 block w-3/4"
                    placeholder="Customer Email"
                ></TextInput>
                <InputError
                    :message="form.errors.customer_email"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3 mt-6">
                <InputLabel
                    for="customer_address"
                    value="Customer Address"
                ></InputLabel>
                <TextInput
                    id="customer_address"
                    ref="nameInput"
                    v-model="form.customer_address"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Customer Address"
                ></TextInput>
                <InputError
                    :message="form.errors.customer_address"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3" v-if="title === 'Edit Entry'">
                <InputLabel for="status" value="Status"></InputLabel>
                <select
                    id="status"
                    v-model="form.status"
                    class="mt-1 block w-3/4 rounded-md border-gray-300 shadow-sm focus:ring focus:ring-indigo-200"
                >
                    <option value="" disabled>Select a status</option>
                    <option value="Paid">Paid</option>
                    <option value="Shipped">Shipped</option>
                    <option value="Received">Received</option>
                    <option value="Completed">Completed</option>
                    <option value="Refunded">Refunded</option>
                </select>
                <InputError
                    :message="form.errors.status"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3 mt-6">
                <PrimaryButton :disabled="form.processing" @click="save"
                    >Save</PrimaryButton
                >
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
